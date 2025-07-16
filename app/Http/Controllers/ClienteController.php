<?php
// app/Http/Controllers/ClientController.php

namespace App\Http\Controllers;
use App\Models\Cliente;
use App\Models\Provincia;
use App\Models\Localidad;
use App\Models\Terminal;
use App\Models\Sucursal;
use App\Models\Tipo_Haber;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Presentacion;
use App\Models\Tipo_Credito;
use App\Models\Tipo_Cuota;
use App\Models\Tipo_Interes;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use GuzzleHttp\Client;

class ClienteController extends Controller
{

// Mostrar formulario para consulta de saldo
    public function consultaDeuda()
    {
        return view('clientes.consulta-deuda');
    }

    // Consultar saldo del cliente
    public function deuda($dni)
    {
        // Buscar cliente por DNI
        $cliente = Cliente::where('dni', $dni)->first();
        $deudaTotal = Cuota::whereHas('credito', function ($query) use ($cliente) {
                $query->where('id_cliente', $cliente->id)
                    ->whereNull('fecha_cancelacion');
                    })->sum('a_pagar');

        // Si no se encuentra el cliente
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Retornar datos del cliente
        return response()->json([
            'nombre' => $cliente->nombre,
            'deudaTotal' => $deudaTotal, // Asume que hay una relación 'credito' en el modelo Cliente

            
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $productos = Cliente::where('dni', 'like', "%$query%")
                            ->orWhere('nombre', 'like', "%$query%")
                            ->get();
        return response()->json($productos);
    }
    public function searchdni(Request $request)
    {
        $query = $request->input('dni');
        $productos = Cliente::where('dni', 'like', "$query")
                            ->get();
        return response()->json($productos);
    }

    public function nuevoCredito(Cliente $cliente)
    {
        $interes = 0.04; // 4% de interés mensual
        $terminales = Sucursal::orderBy('codigo')->get(); // 
        return view('creditos.nuevoCredito', compact('cliente', 'interes', 'terminales'));
    }


    public function exportarFicha(Cliente $cliente)
    {
        
        //$cliente = Cliente::findOrFail($id); // Asegúrate de que el cliente existe
        $pdf = PDF::loadView('clientes.exportarFicha', compact('cliente'));
        //return view('clientes.exportarFicha', compact('cliente'));
        return $pdf->stream();
        //return $pdf->download('ficha_cliente_' . $cliente->id . '.pdf');
    }


    public function imprimirMovimientos(Cliente $cliente)
    {
        $imprimir = true;
        $creditos_pendientes = $cliente->creditos()->whereNull('fecha_cancelacion')->orderBy('fecha_credito', 'desc')->get();
        $creditos_pagados = $cliente->creditos()->whereNotNull('fecha_cancelacion')->orderBy('fecha_credito', 'desc')->get();
        return View('clientes.movimientos', compact('cliente', 'creditos_pendientes', 'creditos_pagados', 'imprimir'));
    }


    public function movimientos(Cliente $cliente)
    {
        //$cliente = Cliente::findOrFail($id);

        // Obtener los créditos del cliente y ordenarlos de forma descendente
        //$creditos = $cliente->creditos()->orderBy('credito', 'desc')->get();

        $creditos_pendientes = $cliente->creditos()->whereNull('fecha_cancelacion')->orderBy('fecha_credito', 'desc')->get();
        $creditos_pagados = $cliente->creditos()->whereNotNull('fecha_cancelacion')->orderBy('fecha_credito', 'desc')->get();
        $deudaTotal = Cuota::whereHas('credito', function ($query) use ($cliente) {
                        $query->where('id_cliente', $cliente->id)
                            ->whereNull('fecha_cancelacion');
                            })->sum('a_pagar');

        //$creditos = $cliente->creditos; // Asumiendo que tienes una relación definida

        return view('clientes.movimientos', compact('cliente', 'creditos_pendientes', 'creditos_pagados', 'deudaTotal'));
    }

    public function imprimirFicha(Cliente $cliente)
    {
        return View('clientes.exportarFicha', compact('cliente'));
    }

    public function index(Request $request)
    {
        // Inicializa la variable $clientes como una colección vacía
        $clientes = collect();
    
        // Verifica si hay una consulta de búsqueda

            $query = $request->input('query');
    
            // Realiza la búsqueda en tu modelo de Cliente
            $clientes = Cliente::where('nombre', 'LIKE', "%{$query}%")
                ->orWhere('dni', 'LIKE', "%{$query}%")
                ->orderBy('nombre', 'asc')
                ->paginate(20)  // Esto devuelve una colección dividida en paginas de 20
                ->appends(['query' => $query]); // Mantiene el parámetro de búsqueda en la paginación
        
    
        return view('clientes.index', compact('clientes'));
    }

    public function showFormBuscar()
    {
        return view('clientes.formBuscar');
    }


    public function create()
    {
        //$localidades = Localidad::orderBy('nombre')->get();;
        $provincias = Provincia::orderBy('nombre')->get(); // 
        $sucursales = Sucursal::orderBy('nombre')->get(); // 
        $tipos_haber = Tipo_Haber::orderBy('id')->get(); // 
        //return $sucursales;

        return view('clientes.create', compact('sucursales', 'tipos_haber', 'provincias'));

    }

    public function store(Request $request)
    {
        /* $request->validate([
            'dni' => ' unique:clientes,dni|required|integer',
            'nombre' => 'required|string|max:255',
            'medio_pago_preferido' => 'integer|nullable',
            'cbu' => 'nullable|string|max:25',
            'sucursal' => 'required|exists:sucursales,id',
            'tipo_haber' => 'required|exists:tipo_haberes,id',
            'saldo' => 'nullable|numeric',
            'limite' => 'nullable|numeric',
            'max_cuotas' => 'nullable|integer',
            'suspendido' => 'boolean',
            'obs' => 'nullable|string',
            'direccion' => 'required|string|max:255',
            'id_localidad' => 'required|exists:localidades,id',
            'email' => 'required|email|max:255',
            'tel1' => 'required|string|max:20',
            'tel2' => 'nullable|string|max:20',
            'familiar' => 'nullable|string|max:255',
            'tipo_familiar' => 'nullable|string|max:255',
            'direccion_familiar' => 'nullable|string|max:255',
            'id_localidad_familiar' => 'nullable|exists:localidades,id',
            'email_familiar' => 'nullable|email|max:255',
            'tel_familiar' => 'nullable|string|max:20',
            'trabajo' => 'nullable|string|max:255',
            'direccion_trabajo' => 'nullable|string|max:255',
            'id_localidad_trabajo' => 'nullable|exists:localidades,id',
            'tel_trabajo' => 'nullable|string|max:20',
        ]); */
        
        try {
            $cliente = Cliente::create($request->all());
            $cliente->suspendido = $request->has('suspendido') ? 1 : 0;
            $cliente->save();
            return redirect()->route('clientes.show', ['cliente' => $cliente->id])->with('success', 'Cliente creado con éxito.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Código de error para violación de restricción de unicidad
                return redirect()->back()->withErrors(['dni' => 'El DNI ya está registrado.']);
            }
            // Manejar otros tipos de excepciones si es necesario
        }
    }

    public function edit(Cliente $cliente) // AL TENER EL MODELO EN EL PARAMETRO, AUTOMATICAMENTE DEVIELVE EL OBJETO QUE COINCIDE CON EL ID
    { 
        // Cargar las relaciones necesarias
        $cliente->load('localidad.provincia', 'localidadFamiliar.provincia', 'localidadTrabajo.provincia'); // Carga la localidad y la provincia asociada

        $provincias = Provincia::orderBy('nombre')->get(); // 
        $sucursales = Sucursal::orderBy('nombre')->get(); // 
        $tipos_haber = Tipo_Haber::orderBy('id')->get(); // 

        
        return view('clientes.edit', compact('cliente','sucursales', 'tipos_haber', 'provincias'));
    }


    public function show(Cliente $cliente) // AL TENER EL MODELO EN EL PARAMETRO, AUTOMATICAMENTE DEVIELVE EL OBJETO QUE COINCIDE CON EL ID
    {

        // Cargar las relaciones necesarias
        $cliente->load('localidad.provincia', 'localidadFamiliar.provincia', 'localidadTrabajo.provincia'); // Carga la localidad y la provincia asociada

        $provincias = Provincia::orderBy('nombre')->get(); // Obtener todas las provincias
        $sucursales = Sucursal::orderBy('nombre')->get(); // Obtener todas las sucursales
        $tipos_haber = Tipo_Haber::orderBy('id')->get(); // Obtener todos los tipos de haber

        return view('clientes.show', compact('cliente', 'sucursales', 'tipos_haber', 'provincias'));


    }

    public function update(Request $request, Cliente $cliente)
    {
       /* $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $cliente->id,
            'phone' => 'nullable|string|max:15',
        ]); */

        $cliente->update($request->all());
        $cliente->suspendido = $request->has('suspendido') ? 1 : 0;
        $cliente->save();

        return redirect()->route('clientes.show', ['cliente' => $cliente->id])->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy(Cliente $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Cliente eliminado con éxito.');
    }



}