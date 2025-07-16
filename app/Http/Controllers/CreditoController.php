<?php

namespace App\Http\Controllers;
use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Http\Request;

class CreditoController extends Controller
{
    public function index(Request $request)
{
    $query = $request->input('query');
    $sort_by = $request->input('sort_by', 'fecha_credito');
    $sort_order = $request->input('sort_order', 'desc');
    $activo = $request->input('activo', false);
    $sucursal_id = $request->input('sucursal_id');

    // Buscar el código de sucursal si viene sucursal_id
    $codigoSucursal = null;
    if ($sucursal_id) {
        $sucursal = \App\Models\Sucursal::find($sucursal_id);
        if ($sucursal) {
            $codigoSucursal = $sucursal->codigo;
        }
    }

    $creditosQuery = Credito::with(['sucursal', 'cliente']);

    // Ordenamiento por nombre o dni (relación cliente)
    if (in_array($sort_by, ['nombre', 'dni'])) {
        $creditosQuery->join('clientes', 'creditos.id_cliente', '=', 'clientes.id')
            ->select('creditos.*')
            ->orderBy("clientes.$sort_by", $sort_order);
    } else {
        $creditosQuery->orderBy($sort_by, $sort_order);
    }

    // Filtros
    $creditosQuery
        ->when($query, function ($queryBuilder) use ($query) {
            $keywords = explode(' ', $query);
            foreach ($keywords as $keyword) {
                $queryBuilder->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('credito', 'like', "%$keyword%")
                        ->orWhereHas('cliente', function ($q) use ($keyword) {
                            $q->where('nombre', 'like', "%$keyword%")
                                ->orWhere('dni', 'like', "%$keyword%");
                        })
                        ->orWhereRaw("CONCAT(credito, codigo_sucursal) LIKE ?", ["%$keyword%"]);
                });
            }
        })
        ->when($activo, function ($queryBuilder) {
            $queryBuilder->whereNull('fecha_cancelacion');
        })
        ->when($codigoSucursal, function ($queryBuilder) use ($codigoSucursal) {
            $queryBuilder->where('codigo_sucursal', $codigoSucursal);
        });

    $creditos = $creditosQuery->paginate(20);

    return view('creditos.index', compact('creditos', 'sort_by', 'sort_order', 'query', 'activo', 'sucursal_id'));
}

    public function imprimirPagare($id)
    {
        $credito = Credito::with(['sucursal', 'cliente'])->findOrFail($id);
        return view('creditos.pagare', compact('credito'));
    }

    public function show($id)
    {
        $credito = Credito::with(['sucursal', 'cliente'])->findOrFail($id);
        return view('creditos.show', compact('credito'));
    }
}