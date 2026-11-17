<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use App\Models\Localidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UbicacionController extends Controller
{
    /**
     * Vista consolidada: catálogo de provincias (panel izquierdo) + data-grid de
     * localidades con filtros (panel derecho).
     */
    public function index(Request $request)
    {
        $localidades = Localidad::with('provincia')
            ->when($request->filled('id_provincia'), fn ($q) => $q->where('id_provincia', $request->id_provincia))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->search);
                $q->where(function ($sub) use ($s) {
                    $sub->where('nombre', 'like', "%{$s}%")
                        ->orWhere('codigo_postal', 'like', "%{$s}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $provincias = Provincia::withCount('localidades')
            ->orderBy('nombre')
            ->get();

        return view('sistema.ubicaciones.index', compact('localidades', 'provincias'));
    }

    /* ================= PROVINCIAS ================= */

    public function storeProvincia(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:provincias,nombre',
        ], [], ['nombre' => 'nombre de la provincia']);

        Provincia::create($validated);

        return back()->with('success', 'Provincia registrada exitosamente.');
    }

    public function updateProvincia(Request $request, Provincia $provincia)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('provincias', 'nombre')->ignore($provincia->id),
            ],
        ], [], ['nombre' => 'nombre de la provincia']);

        $provincia->update($validated);

        return back()->with('success', 'Provincia actualizada exitosamente.');
    }

    public function destroyProvincia(Provincia $provincia)
    {
        if ($provincia->localidades()->exists()) {
            return back()->with('error', 'No se puede eliminar la provincia porque tiene localidades asociadas.');
        }

        $provincia->delete();

        return back()->with('success', 'Provincia eliminada exitosamente.');
    }

    /* ================= LOCALIDADES ================= */

    public function storeLocalidad(Request $request)
    {
        $validated = $request->validate([
            'id_provincia' => 'required|exists:provincias,id',
            'nombre' => 'required|string|max:191',
            'codigo_postal' => 'nullable|string|max:10',
        ], [], [
            'id_provincia' => 'provincia',
            'codigo_postal' => 'código postal',
        ]);

        $validated['nombre'] = ucwords(strtolower(trim($validated['nombre'])));

        Localidad::create($validated);

        return back()->with('success', 'Localidad registrada exitosamente.');
    }

    public function updateLocalidad(Request $request, Localidad $localidad)
    {
        $validated = $request->validate([
            'id_provincia' => 'required|exists:provincias,id',
            'nombre' => 'required|string|max:191',
            'codigo_postal' => 'nullable|string|max:10',
        ], [], [
            'id_provincia' => 'provincia',
            'codigo_postal' => 'código postal',
        ]);

        $validated['nombre'] = ucwords(strtolower(trim($validated['nombre'])));

        $localidad->update($validated);

        return back()->with('success', 'Localidad actualizada exitosamente.');
    }

    public function destroyLocalidad(Localidad $localidad)
    {
        if ($localidad->clientes()->exists()
            || $localidad->clientesFamiliar()->exists()
            || $localidad->clientesTrabajo()->exists()) {
            return back()->with('error', 'No se puede eliminar la localidad porque tiene clientes asociados.');
        }

        $localidad->delete();

        return back()->with('success', 'Localidad eliminada exitosamente.');
    }
}
