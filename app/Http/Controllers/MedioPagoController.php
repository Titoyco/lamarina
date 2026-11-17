<?php

namespace App\Http\Controllers;

use App\Models\MedioPago;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MedioPagoController extends Controller
{
    /**
     * Listado general de medios de pago con estadísticas de uso.
     */
    public function index(Request $request)
    {
        $query = MedioPago::withCount(['ventas', 'cuotas', 'recibos']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_base') && $request->tipo_base !== 'all') {
            $query->where('tipo_base', $request->tipo_base);
        }

        if ($request->filled('estado') && $request->estado !== 'all') {
            $query->where('activo', $request->estado === 'activo');
        }

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'orden'              => 'medios_pago.orden',
            'nombre'             => 'medios_pago.nombre',
            'codigo'             => 'medios_pago.codigo',
            'tipo_base'          => 'medios_pago.tipo_base',
            'recargo_porcentaje' => 'medios_pago.recargo_porcentaje',
            'activo'             => 'medios_pago.activo',
            'movimientos'        => 'ventas_count',
        ];

        $sort = $request->get('sort', 'orden');
        $direction = strtolower($request->get('direction', 'asc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'orden';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query->orderBy($sortableColumns[$sort], $direction);

        if ($sort !== 'id' && $sort !== 'orden') {
            $query->orderBy('medios_pago.orden', 'asc');
        }

        $medios = $query->paginate(20)->withQueryString();

        return view('sistema.medios_pago.index', compact('medios', 'sort', 'direction'));
    }

    /**
     * Formulario de alta para un nuevo medio de pago.
     */
    public function create()
    {
        $medio = new MedioPago([
            'tipo_base'            => 'otro',
            'recargo_porcentaje'   => 0.00,
            'descuento_porcentaje' => 0.00,
            'requiere_referencia'  => false,
            'permite_vuelto'       => false,
            'icono'                => '💳',
            'color'                => 'indigo',
            'orden'                => MedioPago::max('orden') + 1,
            'activo'               => true,
        ]);

        return view('sistema.medios_pago.form', [
            'medio'    => $medio,
            'isEdit'   => false,
            'movsCount'=> 0,
        ]);
    }

    /**
     * Registra un nuevo medio de pago en la base de datos.
     */
    public function store(Request $request)
    {
        if ($request->filled('codigo')) {
            $request->merge(['codigo' => Str::slug(str_replace(' ', '_', $request->codigo), '_')]);
        }

        $validated = $request->validate([
            'codigo'               => 'required|string|max:50|alpha_dash|unique:medios_pago,codigo',
            'nombre'               => 'required|string|max:100',
            'tipo_base'            => 'required|in:efectivo,tarjeta,debito,tarjeta_credito,credito,banco,transferencia,cheque,otro,otros',
            'recargo_porcentaje'   => 'nullable|numeric|min:0|max:100',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'requiere_referencia'  => 'nullable|boolean',
            'permite_vuelto'       => 'nullable|boolean',
            'icono'                => 'nullable|string|max:50',
            'color'                => 'nullable|string|max:50',
            'orden'                => 'nullable|integer|min:0',
            'activo'               => 'nullable|boolean',
        ], [
            'codigo.unique' => 'El código identificador ya está en uso por otro medio de pago.',
            'codigo.alpha_dash' => 'El código solo puede contener letras, números, guiones y guiones bajos.',
        ]);

        $validated['recargo_porcentaje'] = (float)($validated['recargo_porcentaje'] ?? 0);
        $validated['descuento_porcentaje'] = (float)($validated['descuento_porcentaje'] ?? 0);
        $validated['requiere_referencia'] = $request->boolean('requiere_referencia');
        $validated['permite_vuelto'] = $request->boolean('permite_vuelto');
        $validated['activo'] = $request->boolean('activo', true);
        $validated['orden'] = (int)($validated['orden'] ?? 0);
        $validated['icono'] = $validated['icono'] ?: '💳';
        $validated['color'] = $validated['color'] ?: 'indigo';

        $medio = MedioPago::create($validated);

        return redirect()->route('sistema.medios-pago.index')
            ->with('success', "Medio de pago '{$medio->nombre}' creado exitosamente.");
    }

    /**
     * Formulario de edición de un medio de pago existente.
     */
    public function edit(MedioPago $medioPago)
    {
        $movsCount = $medioPago->ventas()->count() + $medioPago->cuotas()->count() + $medioPago->recibos()->count();

        return view('sistema.medios_pago.form', [
            'medio'     => $medioPago,
            'isEdit'    => true,
            'movsCount' => $movsCount,
        ]);
    }

    /**
     * Actualiza un medio de pago existente.
     */
    public function update(Request $request, MedioPago $medioPago)
    {
        if ($request->filled('codigo')) {
            $request->merge(['codigo' => Str::slug(str_replace(' ', '_', $request->codigo), '_')]);
        }

        $validated = $request->validate([
            'codigo'               => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('medios_pago', 'codigo')->ignore($medioPago->id)],
            'nombre'               => 'required|string|max:100',
            'tipo_base'            => 'required|in:efectivo,tarjeta,debito,tarjeta_credito,credito,banco,transferencia,cheque,otro,otros',
            'recargo_porcentaje'   => 'nullable|numeric|min:0|max:100',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'requiere_referencia'  => 'nullable|boolean',
            'permite_vuelto'       => 'nullable|boolean',
            'icono'                => 'nullable|string|max:50',
            'color'                => 'nullable|string|max:50',
            'orden'                => 'nullable|integer|min:0',
            'activo'               => 'nullable|boolean',
        ]);

        $validated['recargo_porcentaje'] = (float)($validated['recargo_porcentaje'] ?? 0);
        $validated['descuento_porcentaje'] = (float)($validated['descuento_porcentaje'] ?? 0);
        $validated['requiere_referencia'] = $request->boolean('requiere_referencia');
        $validated['permite_vuelto'] = $request->boolean('permite_vuelto');
        $validated['activo'] = $request->boolean('activo', true);
        $validated['orden'] = (int)($validated['orden'] ?? 0);
        $validated['icono'] = $validated['icono'] ?: '💳';
        $validated['color'] = $validated['color'] ?: 'indigo';

        $medioPago->update($validated);

        return redirect()->route('sistema.medios-pago.index')
            ->with('success', "Medio de pago '{$medioPago->nombre}' actualizado correctamente.");
    }

    /**
     * Habilita o deshabilita rápidamente un medio de pago.
     */
    public function toggleActivo(MedioPago $medioPago)
    {
        $medioPago->update(['activo' => !$medioPago->activo]);

        $estado = $medioPago->activo ? 'activado' : 'desactivado';
        return redirect()->route('sistema.medios-pago.index')->with('success', "Medio de pago '{$medioPago->nombre}' {$estado} correctamente.");
    }

    /**
     * Elimina un medio de pago si y sólo si no tiene movimientos asociados.
     */
    public function destroy(MedioPago $medioPago)
    {
        $cantVentas = $medioPago->ventas()->count();
        $cantCuotas = $medioPago->cuotas()->count();
        $cantRecibos = $medioPago->recibos()->count();
        $totalMovs = $cantVentas + $cantCuotas + $cantRecibos;

        if ($totalMovs > 0) {
            $detalles = [];
            if ($cantVentas > 0) $detalles[] = "{$cantVentas} ventas";
            if ($cantCuotas > 0) $detalles[] = "{$cantCuotas} pagos de cuotas";
            if ($cantRecibos > 0) $detalles[] = "{$cantRecibos} recibos de cobranza";
            $detalleStr = implode(', ', $detalles);

            return redirect()->route('sistema.medios-pago.index')->with('error', "No se puede eliminar el medio de pago '{$medioPago->nombre}' porque tiene {$totalMovs} movimientos asociados ({$detalleStr}). Para impedir su uso en nuevas operaciones, desactívelo.");
        }

        $nombre = $medioPago->nombre;
        $medioPago->delete();

        return redirect()->route('sistema.medios-pago.index')
            ->with('success', "Medio de pago '{$nombre}' eliminado exitosamente.");
    }
}
