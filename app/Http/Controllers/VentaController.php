<?php

namespace App\Http\Controllers;

use App\Models\Venta; // Asegúrate de tener el modelo Venta creado

use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
       
        return view('ventas.index');
    }

    public function show($id)
    {
        
        return view('ventas.show');
    }

    public function create()
    {
        return view('ventas.create');
    }

    public function store(Request $request)
    {


        return response()->json($request);//redirect()->route('ventas.index')->with('success', 'Venta creada correctamente.');
    }



}



/*

    public function index()
    {
        $ventas = Venta::all(); // Obtener todas las ventas
        return view('ventas.index', compact('ventas'));
    }

    public function show($id)
    {
        $venta = Venta::findOrFail($id); // Obtener una venta por ID o lanzar un error 404
        return view('ventas.show', compact('venta'));
    }

    public function create()
    {
        return view('ventas.create');
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id', // Asegúrate de que la tabla clientes exista
            'fecha_venta' => 'required|date',
            'total_venta' => 'required|numeric',
            // Agrega más validaciones según tus necesidades
        ]);

        // Crear una nueva venta
        Venta::create($request->all());

        return redirect()->route('ventas.index')->with('success', 'Venta creada correctamente.');
    }

    public function edit($id)
    {
        $venta = Venta::findOrFail($id);
        return view('ventas.edit', compact('venta'));
    }

    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_venta' => 'required|date',
            'total_venta' => 'required|numeric',
            // Agrega más validaciones según tus necesidades
        ]);

        $venta = Venta::findOrFail($id);
        $venta->update($request->all());

        return redirect()->route('ventas.index')->with('success', 'Venta actualizada correctamente.');
    }

    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);
        $venta->delete();

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
    }

*/