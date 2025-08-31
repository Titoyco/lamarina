<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\ProductoMovimiento;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    // Ingresar stock a una sucursal
    public function ingresarStock(Request $request)
    {
        $request->validate([
            'producto_id'   => 'required|exists:productos,id',
            'sucursal_id'   => 'required|exists:sucursales,id',
            'cantidad'      => 'required|integer|min:1',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'nullable|numeric|min:0',
            'punto_compra'  => 'nullable|numeric|min:0'
        ]);

        DB::transaction(function () use ($request) {
            // Registrar el ingreso en movimientos
            ProductoMovimiento::create([
                'producto_id'   => $request->producto_id,
                'sucursal_id'   => $request->sucursal_id,
                'tipo'          => 'ingreso',
                'cantidad'      => $request->cantidad,
                'precio_compra' => $request->precio_compra,
                'precio_venta'  => $request->precio_venta,
                'usuario_id'    => auth()->id(),
            ]);

            $producto = Producto::findOrFail($request->producto_id);
            $existingPivot = $producto->sucursales()
                ->where('sucursal_id', $request->sucursal_id)
                ->first();

            $datosPivot = [
                'stock'        => $request->cantidad,
                'precio_venta' => $request->precio_venta ?? 0,
                'punto_compra' => $request->punto_compra ?? 0
            ];

            if ($existingPivot) {
                // Sumar stock, actualizar precios si se envían
                $nuevoStock = $existingPivot->pivot->stock + $request->cantidad;
                $datosPivot['stock'] = $nuevoStock;
                if ($request->precio_venta === null) {
                    $datosPivot['precio_venta'] = $existingPivot->pivot->precio_venta;
                }
                if ($request->punto_compra === null) {
                    $datosPivot['punto_compra'] = $existingPivot->pivot->punto_compra;
                }
                $producto->sucursales()->updateExistingPivot($request->sucursal_id, $datosPivot);
            } else {
                $producto->sucursales()->attach($request->sucursal_id, $datosPivot);
            }
        });

        return redirect()->back()->with('success', 'Stock ingresado correctamente');
    }

    // Formulario para modificar stock de un producto en la sucursal activa
    public function modificarStockForm($id)
    {
        $producto = Producto::findOrFail($id);
        $sucursalId = session('sucursal_activa');
        $sucursal = Sucursal::findOrFail($sucursalId);

        // Buscar el registro de productos_stock (puede no existir)
        $pivot = $producto->sucursales()->where('sucursal_id', $sucursalId)->first();
        $stock_actual = $pivot ? $pivot->pivot->stock : 0;

        return view('productos.stock.modificar', compact('producto', 'sucursal', 'stock_actual'));
    }

    public function modificarStock(Request $request, $id)
    {
        $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        $producto = Producto::findOrFail($id);
        $sucursalId = session('sucursal_activa');
        $nuevoStock = $request->input('stock');

        $pivot = $producto->sucursales()->where('sucursal_id', $sucursalId)->first();
        $stockAnterior = $pivot ? $pivot->pivot->stock : 0;
        $diferencia = $nuevoStock - $stockAnterior;

        DB::transaction(function () use ($producto, $sucursalId, $nuevoStock, $diferencia, $stockAnterior) {
            // Registrar corrección solo si hay diferencia
            if ($diferencia !== 0) {
                ProductoMovimiento::create([
                    'producto_id'   => $producto->id,
                    'sucursal_id'   => $sucursalId,
                    'tipo'          => 'correccion',
                    'cantidad'      => $diferencia,
                    'usuario_id'    => auth()->id(),
                    'motivo'        => 'Ajuste manual por modificación de stock',
                ]);
            }

            // Actualizar o crear el registro de productos_stock
            $pivot = $producto->sucursales()->where('sucursal_id', $sucursalId)->first();
            if ($pivot) {
                $producto->sucursales()->updateExistingPivot($sucursalId, [
                    'stock' => $nuevoStock
                ]);
            } else {
                $producto->sucursales()->attach($sucursalId, [
                    'stock' => $nuevoStock,
                    'precio_venta' => 0,
                    'punto_compra' => 0
                ]);
            }
        });

        return redirect()->route('productos.show', $producto->id)
            ->with('success', 'Stock actualizado correctamente para la sucursal activa.');
    }

    // Transferir stock entre sucursales
    public function transferirStock(Request $request)
    {
        $request->validate([
            'producto_id'           => 'required|exists:productos,id',
            'sucursal_origen_id'    => 'required|exists:sucursales,id',
            'sucursal_destino_id'   => 'required|exists:sucursales,id|different:sucursal_origen_id',
            'cantidad'              => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($request) {
            $producto = Producto::findOrFail($request->producto_id);

            // Stock en origen
            $stockOrigen = $producto->sucursales()
                ->where('sucursal_id', $request->sucursal_origen_id)
                ->first();

            if (!$stockOrigen || $stockOrigen->pivot->stock < $request->cantidad) {
                throw ValidationException::withMessages(['cantidad' => 'Stock insuficiente en sucursal origen']);
            }

            // Disminuir stock en origen
            $nuevoStockOrigen = $stockOrigen->pivot->stock - $request->cantidad;
            $producto->sucursales()->updateExistingPivot($request->sucursal_origen_id, [
                'stock' => $nuevoStockOrigen
            ]);

            // Registrar movimiento salida en origen (negativo)
            ProductoMovimiento::create([
                'producto_id'        => $producto->id,
                'sucursal_id'        => $request->sucursal_origen_id,
                'tipo'               => 'traslado',
                'cantidad'           => -$request->cantidad,
                'usuario_id'         => auth()->id(),
                'sucursal_origen_id' => $request->sucursal_origen_id,
                'motivo'             => 'Traslado a sucursal destino',
            ]);

            // Aumentar stock en destino
            $stockDestino = $producto->sucursales()
                ->where('sucursal_id', $request->sucursal_destino_id)
                ->first();

            $pivotDestino = [
                'stock'        => $request->cantidad,
                'precio_venta' => $stockOrigen->pivot->precio_venta,
                'punto_compra' => $stockOrigen->pivot->punto_compra
            ];

            if ($stockDestino) {
                $nuevoStockDestino = $stockDestino->pivot->stock + $request->cantidad;
                $pivotDestino['stock'] = $nuevoStockDestino;
                $producto->sucursales()->updateExistingPivot($request->sucursal_destino_id, $pivotDestino);
            } else {
                $producto->sucursales()->attach($request->sucursal_destino_id, $pivotDestino);
            }

            // Registrar movimiento entrada en destino (positivo)
            ProductoMovimiento::create([
                'producto_id'        => $producto->id,
                'sucursal_id'        => $request->sucursal_destino_id,
                'tipo'               => 'traslado',
                'cantidad'           => $request->cantidad,
                'usuario_id'         => auth()->id(),
                'sucursal_origen_id' => $request->sucursal_origen_id,
                'motivo'             => 'Recepción de traslado desde sucursal origen',
            ]);
        });

        return redirect()->back()->with('success', 'Stock transferido correctamente');
    }

    // API: Obtener stock de un producto en todas las sucursales
    public function getStockPorSucursales($producto_id)
    {
        $producto = Producto::with('sucursales')->findOrFail($producto_id);
        $result = $producto->sucursales->map(function ($sucursal) {
            return [
                'sucursal_id'   => $sucursal->id,
                'sucursal'      => $sucursal->nombre,
                'stock'         => $sucursal->pivot->stock,
                'precio_venta'  => $sucursal->pivot->precio_venta,
                'punto_compra'  => $sucursal->pivot->punto_compra
            ];
        });

        return response()->json($result);
    }

    // Productos con stock bajo (por debajo del punto de compra)
    public function productosStockBajo()
    {
        $productos = Producto::with(['sucursales' => function($query) {
            $query->whereRaw('productos_stock.stock <= productos_stock.punto_compra');
        }])->get()->filter(function($producto) {
            return $producto->sucursales->count() > 0;
        });

        return view('productos.stock-bajo', compact('productos'));
    }
}