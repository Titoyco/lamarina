<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Services\ArcaWsfeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PuntoVentaController extends Controller
{
    /**
     * Listado de puntos de venta con filtros y paginación.
     */
    public function index(Request $request)
    {
        $query = PuntoVenta::with('sucursal');

        if ($request->filled('id_sucursal')) {
            $query->where('id_sucursal', $request->id_sucursal);
        }

        if ($request->filled('tipo_emision')) {
            $query->where('tipo_emision', $request->tipo_emision);
        }

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'numero'       => 'puntos_venta.numero',
            'nombre'       => 'puntos_venta.nombre',
            'sucursal'     => 'sucursal',
            'tipo_emision' => 'puntos_venta.tipo_emision',
            'activo'       => 'puntos_venta.activo',
        ];

        $sort = $request->get('sort', 'numero');
        $direction = strtolower($request->get('direction', 'asc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'numero';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        if ($sort === 'sucursal') {
            $query->leftJoin('sucursales', 'puntos_venta.id_sucursal', '=', 'sucursales.id')
                ->select('puntos_venta.*')
                ->orderBy('sucursales.nombre', $direction);
        } else {
            $query->orderBy($sortableColumns[$sort], $direction);
        }

        if ($sort !== 'id' && $sort !== 'numero') {
            $query->orderBy('puntos_venta.numero', 'asc');
        }

        $puntosVenta = $query->paginate(15)->withQueryString();

        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('sistema.puntos-venta.index', compact('puntosVenta', 'sucursales', 'sort', 'direction'));
    }

    /**
     * Formulario para crear un nuevo punto de venta.
     */
    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        return view('sistema.puntos-venta.create', compact('sucursales'));
    }

    /**
     * Almacena un nuevo punto de venta en la base de datos.
     */
    public function store(Request $request)
    {
        $rules = [
            'id_sucursal'   => 'required|exists:sucursales,id',
            'numero'        => [
                'required',
                'integer',
                'min:1',
                'max:99999',
                Rule::unique('puntos_venta')->where(fn ($query) => $query->where('id_sucursal', $request->id_sucursal)),
            ],
            'nombre'        => 'required|string|max:100',
            'tipo_emision'  => 'required|in:fiscal_hasar,arca_wsfe',
            'activo'        => 'nullable|boolean',
        ];

        if ($request->tipo_emision === 'fiscal_hasar') {
            $rules['hasar_ip']     = 'required|ipv4';
            $rules['hasar_puerto'] = 'required|integer|min:1|max:65535';
            $rules['puerto_local'] = 'required|integer|min:1024|max:65535';
            $rules['modelo']       = 'nullable|string|max:50';
        } elseif ($request->tipo_emision === 'arca_wsfe') {
            $rules['ambiente']      = 'required|in:homologacion,produccion';
            $rules['cuit']          = 'required|string|max:14';
            $rules['concepto']      = 'nullable|integer|in:1,2,3';
            $rules['certificado']   = 'nullable|file|max:2048';
            $rules['clave_privada'] = 'nullable|file|max:2048';
        }

        $request->validate($rules);

        $configuracion = [];
        if ($request->tipo_emision === 'fiscal_hasar') {
            $configuracion = [
                'puerto_local' => (int) $request->puerto_local,
                'hasar_ip'     => $request->hasar_ip,
                'hasar_puerto' => (int) $request->hasar_puerto,
                'modelo'       => $request->input('modelo', 'SMH/PT-250F') ?: 'SMH/PT-250F',
            ];
        } elseif ($request->tipo_emision === 'arca_wsfe') {
            $cuitLimpio = preg_replace('/[^0-9]/', '', (string) $request->cuit);
            $configuracion = [
                'ambiente' => $request->ambiente,
                'cuit'     => $cuitLimpio,
                'concepto' => (int) ($request->input('concepto', 1) ?: 1),
            ];

            // Subir Certificado ARCA (.crt/.pem)
            if ($request->hasFile('certificado')) {
                $ext = $request->file('certificado')->getClientOriginalExtension() ?: 'crt';
                $certName = "cert_pv_{$request->numero}_" . time() . ".{$ext}";
                $path = $request->file('certificado')->storeAs('arca/certificados', $certName);
                $configuracion['certificado_path'] = $path;
            }

            // Subir Clave Privada ARCA (.key)
            if ($request->hasFile('clave_privada')) {
                $ext = $request->file('clave_privada')->getClientOriginalExtension() ?: 'key';
                $keyName = "key_pv_{$request->numero}_" . time() . ".{$ext}";
                $path = $request->file('clave_privada')->storeAs('arca/claves', $keyName);
                $configuracion['clave_privada_path'] = $path;
            }
        }

        PuntoVenta::create([
            'id_sucursal'   => $request->id_sucursal,
            'numero'        => (int) $request->numero,
            'nombre'        => $request->nombre,
            'tipo_emision'  => $request->tipo_emision,
            'configuracion' => $configuracion,
            'activo'        => $request->has('activo') ? $request->boolean('activo') : true,
        ]);

        return redirect()->route('sistema.puntos-venta.index')
            ->with('success', 'Punto de venta registrado exitosamente.');
    }

    /**
     * Formulario de edición para un punto de venta.
     */
    public function edit(PuntoVenta $puntoVenta)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        return view('sistema.puntos-venta.edit', compact('puntoVenta', 'sucursales'));
    }

    /**
     * Actualiza el punto de venta en la base de datos.
     */
    public function update(Request $request, PuntoVenta $puntoVenta)
    {
        $rules = [
            'id_sucursal'   => 'required|exists:sucursales,id',
            'numero'        => [
                'required',
                'integer',
                'min:1',
                'max:99999',
                Rule::unique('puntos_venta')
                    ->where(fn ($query) => $query->where('id_sucursal', $request->id_sucursal))
                    ->ignore($puntoVenta->id),
            ],
            'nombre'        => 'required|string|max:100',
            'tipo_emision'  => 'required|in:fiscal_hasar,arca_wsfe',
            'activo'        => 'nullable|boolean',
        ];

        if ($request->tipo_emision === 'fiscal_hasar') {
            $rules['hasar_ip']     = 'required|ipv4';
            $rules['hasar_puerto'] = 'required|integer|min:1|max:65535';
            $rules['puerto_local'] = 'required|integer|min:1024|max:65535';
            $rules['modelo']       = 'nullable|string|max:50';
        } elseif ($request->tipo_emision === 'arca_wsfe') {
            $rules['ambiente']      = 'required|in:homologacion,produccion';
            $rules['cuit']          = 'required|string|max:14';
            $rules['concepto']      = 'nullable|integer|in:1,2,3';
            $rules['certificado']   = 'nullable|file|max:2048';
            $rules['clave_privada'] = 'nullable|file|max:2048';
        }

        $request->validate($rules);

        $prevConfig = $puntoVenta->configuracion ?? [];
        $configuracion = [];

        if ($request->tipo_emision === 'fiscal_hasar') {
            $configuracion = [
                'puerto_local' => (int) $request->puerto_local,
                'hasar_ip'     => $request->hasar_ip,
                'hasar_puerto' => (int) $request->hasar_puerto,
                'modelo'       => $request->input('modelo', 'SMH/PT-250F') ?: 'SMH/PT-250F',
            ];
        } elseif ($request->tipo_emision === 'arca_wsfe') {
            $cuitLimpio = preg_replace('/[^0-9]/', '', (string) $request->cuit);
            $configuracion = [
                'ambiente'           => $request->ambiente,
                'cuit'               => $cuitLimpio,
                'concepto'           => (int) ($request->input('concepto', 1) ?: 1),
                'certificado_path'   => $prevConfig['certificado_path'] ?? null,
                'clave_privada_path' => $prevConfig['clave_privada_path'] ?? null,
            ];

            // Reemplazo de Certificado
            if ($request->hasFile('certificado')) {
                $ext = $request->file('certificado')->getClientOriginalExtension() ?: 'crt';
                $certName = "cert_pv_{$request->numero}_" . time() . ".{$ext}";
                $path = $request->file('certificado')->storeAs('arca/certificados', $certName);
                $configuracion['certificado_path'] = $path;
            }

            // Reemplazo de Clave Privada
            if ($request->hasFile('clave_privada')) {
                $ext = $request->file('clave_privada')->getClientOriginalExtension() ?: 'key';
                $keyName = "key_pv_{$request->numero}_" . time() . ".{$ext}";
                $path = $request->file('clave_privada')->storeAs('arca/claves', $keyName);
                $configuracion['clave_privada_path'] = $path;
            }
        }

        $puntoVenta->update([
            'id_sucursal'   => $request->id_sucursal,
            'numero'        => (int) $request->numero,
            'nombre'        => $request->nombre,
            'tipo_emision'  => $request->tipo_emision,
            'configuracion' => $configuracion,
            'activo'        => $request->has('activo') ? $request->boolean('activo') : false,
        ]);

        return redirect()->route('sistema.puntos-venta.index')
            ->with('success', 'Punto de venta actualizado correctamente.');
    }

    /**
     * Elimina el punto de venta especificado.
     */
    public function destroy(PuntoVenta $puntoVenta)
    {
        $puntoVenta->delete();

        return redirect()->route('sistema.puntos-venta.index')
            ->with('success', 'Punto de venta eliminado correctamente.');
    }

    /**
     * Descarga el archivo config.json listo para el puente local de Windows.
     */
    public function descargarConfig(PuntoVenta $puntoVenta)
    {
        if ($puntoVenta->tipo_emision !== 'fiscal_hasar') {
            return redirect()->back()
                ->with('error', 'El archivo config.json sólo está disponible para puntos de venta con Controlador Fiscal Hasar.');
        }

        $payload = [
            'punto_venta_id'     => $puntoVenta->id,
            'punto_venta_numero' => $puntoVenta->numero,
            'sucursal'           => $puntoVenta->sucursal ? $puntoVenta->sucursal->nombre : 'Sin Sucursal',
            'puerto_local'       => (int) ($puntoVenta->configuracion['puerto_local'] ?? 8585),
            'hasar_ip'           => $puntoVenta->configuracion['hasar_ip'] ?? '',
            'hasar_puerto'       => (int) ($puntoVenta->configuracion['hasar_puerto'] ?? 2000),
            'modelo'             => $puntoVenta->configuracion['modelo'] ?? 'SMH/PT-250F',
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type'        => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="config.json"',
        ]);
    }

    /**
     * Prueba la conexión del Punto de Venta con los servidores de ARCA / AFIP.
     */
    public function probarArca(PuntoVenta $puntoVenta, ArcaWsfeService $arcaService)
    {
        if ($puntoVenta->tipo_emision !== 'arca_wsfe') {
            return response()->json([
                'success' => false,
                'message' => 'Este punto de venta no está configurado para Facturación Digital ARCA.',
            ], 422);
        }

        try {
            $resultado = $arcaService->probarConexion($puntoVenta);
            return response()->json([
                'success' => $resultado['ok'],
                'message' => $resultado['mensaje'],
                'cuit'    => $resultado['cuit'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fallo al conectar con ARCA: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Selecciona y persiste el Punto de Venta de este navegador / terminal en la sesión.
     */
    public function seleccionarTerminal(Request $request)
    {
        $request->validate([
            'id_punto_venta' => 'required|exists:puntos_venta,id',
        ]);

        $pv = PuntoVenta::with('sucursal')->findOrFail($request->id_punto_venta);

        session(['terminal_punto_venta_id' => $pv->id]);
        $request->session()->save();

        return response()->json([
            'success'     => true,
            'message'     => "Terminal configurada en P.V. {$pv->numero_formateado} - {$pv->nombre}",
            'punto_venta' => [
                'id'               => $pv->id,
                'numero'           => $pv->numero,
                'numero_formateado'=> $pv->numero_formateado,
                'nombre'           => $pv->nombre,
                'tipo_emision'     => $pv->tipo_emision,
                'sucursal'         => $pv->sucursal?->nombre,
            ],
        ]);
    }

    /**
     * Devuelve en formato JSON los puntos de venta activos para la sucursal indicada (o la activa).
     */
    public function activosPorSucursal(Request $request)
    {
        $sucursalId = $request->input('id_sucursal', config('app.sucursal_activa.id') ?? 1);

        $puntos = PuntoVenta::where('id_sucursal', $sucursalId)
            ->where('activo', true)
            ->orderBy('numero')
            ->get()
            ->map(fn($p) => [
                'id'               => $p->id,
                'numero'           => $p->numero,
                'numero_formateado'=> $p->numero_formateado,
                'nombre'           => $p->nombre,
                'tipo_emision'     => $p->tipo_emision,
                'puerto_local'     => $p->configuracion['puerto_local'] ?? 8585,
            ]);

        return response()->json([
            'puntos_venta' => $puntos,
        ]);
    }
}
