<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use App\Models\Localidad;
use App\Models\CondicionIva;
use App\Models\Provincia;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::with(['localidad.provincia', 'condicionIva']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('cuit', 'like', "%{$search}%")
                  ->orWhere('legajo', 'like', "%{$search}%")
                  ->orWhere('contacto', 'like', "%{$search}%");
            });
        }

        if ($locId = $request->input('id_localidad')) {
            $query->where('id_localidad', $locId);
        }

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'legajo'    => 'proveedores.legajo',
            'cuit'      => 'proveedores.cuit',
            'nombre'    => 'proveedores.nombre',
            'localidad' => 'localidad',
            'contacto'  => 'proveedores.contacto',
            'saldo'     => 'proveedores.saldo',
        ];

        $sort = $request->get('sort', 'nombre');
        $direction = strtolower($request->get('direction', 'asc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'nombre';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        if ($sort === 'localidad') {
            $query->leftJoin('localidades', 'proveedores.id_localidad', '=', 'localidades.id')
                ->select('proveedores.*')
                ->orderBy('localidades.nombre', $direction);
        } else {
            $query->orderBy($sortableColumns[$sort], $direction);
        }

        if ($sort !== 'id') {
            $query->orderBy('proveedores.id', 'desc');
        }

        // Exportación a Excel / CSV con todos los filtros aplicados
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            $exportQuery = clone $query;
            $filename = 'proveedores_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'Legajo',
                    'CUIT',
                    'Condicion IVA',
                    'Proveedor / Razon Social',
                    'Localidad',
                    'Provincia',
                    'Direccion',
                    'Contacto',
                    'Telefono',
                    'Email',
                    'Saldo Cuenta',
                ], ';');

                $exportQuery->chunk(1000, function ($chunk) use ($handle) {
                    foreach ($chunk as $p) {
                        fputcsv($handle, [
                            $p->legajo ?? '-',
                            $p->cuit ?? '-',
                            $p->condicionIva?->nombre ?? '-',
                            $p->nombre,
                            $p->localidad?->nombre ?? '-',
                            $p->localidad?->provincia?->nombre ?? '-',
                            $p->direccion ?? '-',
                            $p->contacto ?? '-',
                            $p->telefono ?? '-',
                            $p->email ?? '-',
                            number_format((float)$p->saldo, 2, ',', ''),
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $proveedores = $query->paginate(20)->withQueryString();
        $localidades = Localidad::orderBy('nombre')->get();
        $provincias = Provincia::orderBy('nombre')->get();

        return view('proveedores.index', compact('proveedores', 'localidades', 'provincias', 'sort', 'direction'));
    }

    public function create()
    {
        $localidades = Localidad::with('provincia')->orderBy('nombre')->get();
        $condicionesIva = CondicionIva::all();
        $siguienteLegajo = Proveedor::sugerirSiguienteLegajo();

        return view('proveedores.form', compact('localidades', 'condicionesIva', 'siguienteLegajo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'legajo' => 'nullable|string|max:10|unique:proveedores,legajo',
            'nombre' => 'required|string|max:200',
            'cuit' => 'nullable|string|max:20',
            'contacto' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'id_localidad' => 'nullable|exists:localidades,id',
            'id_iva' => 'nullable|exists:condiciones_iva,id',
            'tel1' => 'nullable|string|max:50',
            'tel2' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'web' => 'nullable|string|max:200',
            'observaciones' => 'nullable|string',
        ]);

        $legajo = !empty($validated['legajo']) 
            ? (is_numeric($validated['legajo']) ? str_pad(trim($validated['legajo']), 3, '0', STR_PAD_LEFT) : strtoupper(trim($validated['legajo'])))
            : Proveedor::sugerirSiguienteLegajo();

        $proveedor = Proveedor::create([
            'legajo' => $legajo,
            'nombre' => strtoupper(trim($validated['nombre'])),
            'cuit' => !empty($validated['cuit']) ? preg_replace('/[^0-9]/', '', $validated['cuit']) : null,
            'contacto' => !empty($validated['contacto']) ? trim($validated['contacto']) : null,
            'direccion' => !empty($validated['direccion']) ? trim($validated['direccion']) : null,
            'id_localidad' => $validated['id_localidad'] ?? null,
            'id_iva' => $validated['id_iva'] ?? null,
            'tel1' => $validated['tel1'] ?? null,
            'tel2' => $validated['tel2'] ?? null,
            'email' => $validated['email'] ?? null,
            'web' => $validated['web'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'saldo' => 0,
            'activo' => true,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'proveedor' => [
                    'id' => $proveedor->id,
                    'nombre' => $proveedor->nombre,
                    'legajo' => $proveedor->legajo,
                    'legajo_formateado' => $proveedor->legajo_formateado ?? str_pad($proveedor->legajo, 3, '0', STR_PAD_LEFT),
                ],
                'message' => "Proveedor '{$proveedor->nombre}' registrado correctamente con Legajo [{$proveedor->legajo}]."
            ]);
        }

        return redirect()->route('proveedores.show', $proveedor->id)
                         ->with('success', "Proveedor '{$proveedor->nombre}' registrado correctamente con Legajo [{$proveedor->legajo}].");
    }

    public function show(Proveedor $proveedor)
    {
        $proveedor->load(['localidad.provincia', 'condicionIva', 'productos' => function ($q) {
            $q->take(50);
        }]);

        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        $localidades = Localidad::with('provincia')->orderBy('nombre')->get();
        $condicionesIva = CondicionIva::all();
        $siguienteLegajo = $proveedor->legajo ?? Proveedor::sugerirSiguienteLegajo();

        return view('proveedores.form', compact('proveedor', 'localidades', 'condicionesIva', 'siguienteLegajo'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'legajo' => 'nullable|string|max:10|unique:proveedores,legajo,' . $proveedor->id,
            'nombre' => 'required|string|max:200',
            'cuit' => 'nullable|string|max:20',
            'contacto' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'id_localidad' => 'nullable|exists:localidades,id',
            'id_iva' => 'nullable|exists:condiciones_iva,id',
            'tel1' => 'nullable|string|max:50',
            'tel2' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'web' => 'nullable|string|max:200',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $legajo = !empty($validated['legajo']) 
            ? (is_numeric($validated['legajo']) ? str_pad(trim($validated['legajo']), 3, '0', STR_PAD_LEFT) : strtoupper(trim($validated['legajo'])))
            : $proveedor->legajo;

        $proveedor->update([
            'legajo' => $legajo,
            'nombre' => strtoupper(trim($validated['nombre'])),
            'cuit' => !empty($validated['cuit']) ? preg_replace('/[^0-9]/', '', $validated['cuit']) : null,
            'contacto' => !empty($validated['contacto']) ? trim($validated['contacto']) : null,
            'direccion' => !empty($validated['direccion']) ? trim($validated['direccion']) : null,
            'id_localidad' => $validated['id_localidad'] ?? null,
            'id_iva' => $validated['id_iva'] ?? null,
            'tel1' => $validated['tel1'] ?? null,
            'tel2' => $validated['tel2'] ?? null,
            'email' => $validated['email'] ?? null,
            'web' => $validated['web'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()->route('proveedores.show', $proveedor->id)
                         ->with('success', "Proveedor '{$proveedor->nombre}' actualizado correctamente con Legajo [{$proveedor->legajo}].");
    }

    public function destroy(Proveedor $proveedor)
    {
        if ($proveedor->tieneOperaciones()) {
            return back()->with('error', "No se puede eliminar el proveedor porque posee productos asociados o saldo pendiente.");
        }

        $proveedor->delete();

        return redirect()->route('proveedores.index')
                         ->with('success', "Proveedor eliminado correctamente.");
    }
}
