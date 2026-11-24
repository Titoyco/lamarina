<?php

namespace App\Http\Controllers;

use App\Models\PuntoVenta;
use App\Services\ArcaFacturacionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacturacionController extends Controller
{
    /**
     * Emite un comprobante digital en ARCA (WSFEv1) para un punto de venta.
     * Soporta tanto comprobantes reales de venta como emisiones de prueba en Homologación.
     */
    public function emitirDigital(Request $request, ArcaFacturacionService $arcaService): JsonResponse
    {
        $validated = $request->validate([
            'id_punto_venta' => 'required|exists:puntos_venta,id',
            'cbte_tipo'      => 'nullable',
            'concepto'       => 'nullable|integer|in:1,2,3',
            'doc_tipo'       => 'nullable|integer',
            'doc_nro'        => 'nullable',
            'total'          => 'nullable|numeric|min:0.01',
            'es_prueba'      => 'nullable|boolean',
        ]);

        $pv = PuntoVenta::with('sucursal')->findOrFail($validated['id_punto_venta']);

        if ($pv->tipo_emision !== 'arca_wsfe') {
            return response()->json([
                'success' => false,
                'message' => "El punto de venta #{$pv->numero} ({$pv->nombre}) no está configurado para Facturación Digital ARCA (tipo actual: {$pv->tipo_emision}).",
            ], 422);
        }

        // Si es prueba de homologación y no se especificó monto, emitir por $121.00 ($100 Neto + $21 IVA)
        $total = !empty($validated['total']) ? (float) $validated['total'] : 121.00;
        $cbteTipo = !empty($validated['cbte_tipo']) ? $validated['cbte_tipo'] : 6; // Factura B por defecto

        $datosEmision = [
            'cbte_tipo' => $cbteTipo,
            'concepto'  => $validated['concepto'] ?? 1, // Productos
            'doc_tipo'  => $validated['doc_tipo'] ?? 99, // Consumidor Final
            'doc_nro'   => $validated['doc_nro'] ?? 0,
            'total'     => $total,
        ];

        $resultado = $arcaService->emitirComprobante($pv, $datosEmision);

        if (!$resultado['exito']) {
            return response()->json([
                'success'  => false,
                'message'  => 'No se pudo autorizar el comprobante en ARCA: ' . ($resultado['error'] ?? 'Error desconocido.'),
                'error'    => $resultado['error'] ?? null,
                'ambiente' => $resultado['ambiente'] ?? 'homologacion',
            ], 400);
        }

        return response()->json([
            'success'            => true,
            'message'            => 'Comprobante digital autorizado exitosamente por ARCA.',
            'numero_comprobante' => $resultado['numero_comprobante'],
            'numero_formateado'  => str_pad((string)$resultado['punto_venta'], 5, '0', STR_PAD_LEFT) . '-' . str_pad((string)$resultado['numero_comprobante'], 8, '0', STR_PAD_LEFT),
            'punto_venta'        => $resultado['punto_venta'],
            'cae'                => $resultado['cae'],
            'cae_vencimiento'    => $resultado['cae_vencimiento'],
            'ambiente'           => $resultado['ambiente'],
            'total'              => $resultado['total'],
            'cbte_tipo'          => $resultado['cbte_tipo'],
            'fecha'              => $resultado['fecha'],
        ]);
    }

    /**
     * Consulta el último comprobante autorizado en ARCA para un Punto de Venta.
     */
    public function consultarUltimo(Request $request, ArcaFacturacionService $arcaService): JsonResponse
    {
        $validated = $request->validate([
            'id_punto_venta' => 'required|exists:puntos_venta,id',
            'cbte_tipo'      => 'nullable',
        ]);

        $pv = PuntoVenta::findOrFail($validated['id_punto_venta']);
        $cbteTipo = $validated['cbte_tipo'] ?? 6;

        $res = $arcaService->consultarUltimoComprobante($pv, $cbteTipo);

        if (!$res['exito']) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar ARCA: ' . ($res['error'] ?? 'Error desconocido'),
            ], 400);
        }

        return response()->json([
            'success'            => true,
            'ultimo_comprobante' => $res['ultimo_comprobante'],
            'siguiente_numero'   => $res['siguiente_numero'],
            'punto_venta'        => $res['punto_venta'],
            'ambiente'           => $res['ambiente'],
        ]);
    }
}
