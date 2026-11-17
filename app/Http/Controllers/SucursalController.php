<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SucursalController extends Controller
{
    public function updateGlobal(Request $request)
    {
        // Solo administradores pueden cambiar la sucursal de trabajo
        if (!auth()->user()->is_admin) {
            abort(403, 'Acceso denegado: solo administradores tienen permitido cambiar la sucursal activa.');
        }

        // Validamos que llegue el ID
        $request->validate([
            'id_sucursal' => 'required|exists:sucursales,id'
        ]);

        // Guardamos en sesión de forma persistente
        session(['sucursal_activa_id' => $request->id_sucursal]);
        
        // Forzamos el guardado de la sesión antes de redireccionar
        $request->session()->save();

        return back()->with('success', 'Sucursal activa cambiada exitosamente.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortableColumns = [
            'id'              => 'sucursales.id',
            'codigo_sucursal' => 'sucursales.codigo_sucursal',
            'nombre'          => 'sucursales.nombre',
            'direccion'       => 'sucursales.direccion',
            'cuit'            => 'sucursales.cuit',
            'telefono'        => 'sucursales.telefono',
            'clientes_count'  => 'clientes_count',
        ];

        $sort = $request->get('sort', 'nombre');
        $direction = strtolower($request->get('direction', 'asc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'nombre';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Sucursal::withCount(['clientes', 'creditos']);

        if ($sort === 'clientes_count') {
            $query->orderBy('clientes_count', $direction);
        } else {
            $query->orderBy($sortableColumns[$sort], $direction);
        }

        if ($sort !== 'id') {
            $query->orderBy('sucursales.id', 'asc');
        }

        $sucursales = $query->paginate(15)->withQueryString();

        return view('sistema.sucursales.index', compact('sucursales', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provincias = \App\Models\Provincia::orderBy('nombre')->get();
        return view('sistema.sucursales.create', compact('provincias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'             => 'required|string|max:100',
            'codigo_sucursal'    => 'required|string|max:10|unique:sucursales,codigo_sucursal',
            'id_provincia'       => 'nullable|integer|exists:provincias,id',
            'direccion'          => 'nullable|string|max:191',
            'telefono'           => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:100',
            'pagina_web'         => 'nullable|string|max:150',
            'nombre_fantasia'    => 'nullable|string|max:100',
            'razon_social'       => 'nullable|string|max:150',
            'cuit'               => 'nullable|string|max:25',
            'condicion_iva'      => 'nullable|string|max:100',
            'ingresos_brutos'    => 'nullable|string|max:100',
            'domicilio_fiscal'   => 'nullable|string|max:191',
            'inicio_actividades' => 'nullable|string|max:30',
        ]);

        $validated['codigo_sucursal'] = strtoupper(trim($validated['codigo_sucursal']));
        if (empty($validated['nombre_fantasia'])) {
            $validated['nombre_fantasia'] = 'La Marina';
        }
        if (empty($validated['pagina_web'])) {
            $validated['pagina_web'] = 'www.tiendaslamarina.com.ar';
        }

        Sucursal::create($validated);

        return redirect()->route('sistema.sucursales.index')
            ->with('success', 'Sucursal creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('sistema.sucursales.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sucursal $sucursale)
    {
        $sucursal = $sucursale;
        $provincias = \App\Models\Provincia::orderBy('nombre')->get();
        return view('sistema.sucursales.edit', compact('sucursal', 'provincias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sucursal $sucursale)
    {
        $sucursal = $sucursale;

        $validated = $request->validate([
            'nombre'             => 'required|string|max:100',
            'codigo_sucursal'    => [
                'required',
                'string',
                'max:10',
                Rule::unique('sucursales', 'codigo_sucursal')->ignore($sucursal->id),
            ],
            'id_provincia'       => 'nullable|integer|exists:provincias,id',
            'direccion'          => 'nullable|string|max:191',
            'telefono'           => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:100',
            'pagina_web'         => 'nullable|string|max:150',
            'nombre_fantasia'    => 'nullable|string|max:100',
            'razon_social'       => 'nullable|string|max:150',
            'cuit'               => 'nullable|string|max:25',
            'condicion_iva'      => 'nullable|string|max:100',
            'ingresos_brutos'    => 'nullable|string|max:100',
            'domicilio_fiscal'   => 'nullable|string|max:191',
            'inicio_actividades' => 'nullable|string|max:30',
        ]);

        $validated['codigo_sucursal'] = strtoupper(trim($validated['codigo_sucursal']));
        if (empty($validated['nombre_fantasia'])) {
            $validated['nombre_fantasia'] = 'La Marina';
        }
        if (empty($validated['pagina_web'])) {
            $validated['pagina_web'] = 'www.tiendaslamarina.com.ar';
        }

        $sucursal->update($validated);

        return redirect()->route('sistema.sucursales.index')
            ->with('success', 'Sucursal actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sucursal $sucursale)
    {
        $sucursal = $sucursale;

        if ($sucursal->clientes()->exists() || $sucursal->creditos()->exists()) {
            return redirect()->route('sistema.sucursales.index')
                ->with('error', 'No se puede eliminar la sucursal porque tiene registros vinculados.');
        }

        $sucursal->delete();

        return redirect()->route('sistema.sucursales.index')
            ->with('success', 'Sucursal eliminada exitosamente.');
    }
}

