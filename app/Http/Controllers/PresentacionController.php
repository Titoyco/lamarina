<?php

namespace App\Http\Controllers;

use App\Models\Presentacion;
use App\Models\Credito;
use App\Models\Sucursal;
use App\Models\Provincia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PresentacionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Determinar la provincia a visualizar y gestionar
        if ($user->is_admin) {
            // El administrador puede alternar entre TDF (2) y Chubut (1). Por defecto toma el request o TDF (2)
            $idProvincia = (int) $request->input('provincia_id', 2);
            if (!in_array($idProvincia, [1, 2])) {
                $idProvincia = 2;
            }
        } else {
            // Empleado / Cajero: bloqueado exclusivamente a la provincia de su sucursal
            $idProvincia = $user->provinciaId();
            if ($request->has('provincia_id') && (int) $request->input('provincia_id') !== $idProvincia) {
                abort(403, 'No tienes autorización para gestionar las presentaciones de otra provincia.');
            }
        }

        // Listado de presentaciones para la provincia seleccionada
        $presentaciones = Presentacion::where('id_provincia', $idProvincia)
            ->with('provincia')
            ->orderBy('numero', 'desc')
            ->paginate(15)
            ->appends(['provincia_id' => $idProvincia]);

        // Presentación activa en la provincia seleccionada
        $actual = Presentacion::where('id_provincia', $idProvincia)
            ->whereNull('fecha_fin')
            ->orderBy('numero', 'desc')
            ->first();

        // Estados globales de ambas provincias para el dashboard de control
        $actualTdf = Presentacion::activaParaProvincia(2);
        $actualChubut = Presentacion::activaParaProvincia(1);

        $provincias = Provincia::whereIn('id', [1, 2])->get();
        $sucursalesProvincia = Sucursal::where('id_provincia', $idProvincia)->get();

        return view('sistema.presentaciones.index', compact(
            'presentaciones',
            'actual',
            'actualTdf',
            'actualChubut',
            'idProvincia',
            'provincias',
            'sucursalesProvincia'
        ));
    }

    public function cambiar(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'id_provincia'              => 'required|in:1,2',
            // Se ingresan como porcentaje entero (ej: 6 = 6%) y se guardan como factor (0.06)
            'interes_credito'           => 'required|numeric|min:0|max:100',
            'interes_mora'              => 'required|numeric|min:0|max:100',
            'primera_cuota_sin_interes' => 'nullable|boolean',
        ]);

        $factorCredito = round((float) $request->interes_credito / 100, 4);
        $factorMora = round((float) $request->interes_mora / 100, 4);
        $primeraSinInteres = $request->boolean('primera_cuota_sin_interes');

        $idProvincia = (int) $request->id_provincia;

        // Control de permisos por provincia
        if (!$user->is_admin && $user->provinciaId() !== $idProvincia) {
            return back()->with('error', 'Acceso denegado: solo puedes gestionar las presentaciones de la provincia asignada a tu sucursal.');
        }

        $presentacionActual = Presentacion::where('id_provincia', $idProvincia)
            ->whereNull('fecha_fin')
            ->orderBy('numero', 'desc')
            ->first();

        if (!$presentacionActual) {
            return back()->with('error', 'No hay una presentación abierta actualmente para esta provincia.');
        }

        // 1. Validar regla de los 7 días
        $diasTranscurridos = Carbon::parse($presentacionActual->fecha_inicio)->diffInDays(now());
        if ($diasTranscurridos < 7) {
            return back()->with('error', "No se puede cerrar. Han pasado solo {$diasTranscurridos} días desde la última apertura en esta provincia. (Mínimo 7 días obligatorios).");
        }

        try {
            DB::beginTransaction();

            $ahora = now();
            $tasaMora = (float) ($presentacionActual->interes_mora ?? $factorMora);

            // 2. Cerrar la presentación actual de la provincia
            $presentacionActual->update(['fecha_fin' => $ahora]);

            // 3. Crear la nueva presentación para esta provincia
            $nuevaPresentacion = Presentacion::create([
                'id_provincia'              => $idProvincia,
                'numero'                    => $presentacionActual->numero + 1,
                'fecha_inicio'              => $ahora,
                'fecha_fin'                 => null,
                'interes_credito'           => $factorCredito,
                'interes_mora'              => $factorMora,
                'primera_cuota_sin_interes' => $primeraSinInteres,
            ]);

            $numeroNuevo = $nuevaPresentacion->numero;

            // Obtener IDs de sucursales de esta provincia
            $sucursalesIds = Sucursal::where('id_provincia', $idProvincia)->pluck('id')->toArray();
            if (empty($sucursalesIds)) {
                $sucursalesIds = ($idProvincia === 2) ? [1, 2] : [3, 4];
            }

            // 4. Actualizar cuotas IMPAGAS (saldo_actual > 0) ÚNICAMENTE de créditos de esta provincia
            DB::table('cuotas')
                ->join('creditos', 'cuotas.id_credito', '=', 'creditos.id')
                ->whereIn('creditos.id_sucursal', $sucursalesIds)
                ->where('cuotas.saldo_actual', '>', 0)
                ->update([
                    'cuotas.intereses'    => DB::raw("cuotas.intereses + (cuotas.saldo_actual * {$tasaMora})"),
                    'cuotas.saldo_actual' => DB::raw("cuotas.saldo_actual + (cuotas.saldo_actual * {$tasaMora})")
                ]);

            // 5. Volver exigibles las cuotas del nuevo ciclo en créditos de esta provincia
            DB::table('cuotas')
                ->join('creditos', 'cuotas.id_credito', '=', 'creditos.id')
                ->join('presentaciones', 'creditos.id_presentacion', '=', 'presentaciones.id')
                ->whereIn('creditos.id_sucursal', $sucursalesIds)
                ->where('presentaciones.id_provincia', $idProvincia)
                ->whereRaw('(presentaciones.numero + cuotas.num_cuota) = ?', [$numeroNuevo])
                ->where('cuotas.saldo_actual', '=', 0)
                ->update([
                    'cuotas.saldo_actual' => DB::raw('cuotas.valor_base')
                ]);

            DB::commit();

            $nombreProv = ($idProvincia === 2) ? 'Tierra del Fuego' : 'Chubut';

            return redirect()->route('sistema.presentaciones.index', ['provincia_id' => $idProvincia])
                             ->with('success', "Presentación #{$numeroNuevo} para {$nombreProv} abierta con éxito. Intereses y saldos actualizados.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error grave al procesar el cierre: ' . $e->getMessage());
        }
    }
}