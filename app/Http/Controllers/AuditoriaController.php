<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditoriaController extends Controller
{
    /**
     * Listado paginado de eventos de auditoría con filtros.
     */
    public function index(Request $request)
    {
        $query = Audit::with(['user', 'auditable'])->orderBy('id', 'desc');

        // Filtro por Modelo / Entidad
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        // Filtro por Evento (created, updated, deleted, etc.)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Buscador por ID del registro auditado (auditable_id) o ID de auditoría
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('auditable_id', $search)
                  ->orWhere('id', $search);
            });
        }

        // Filtro opcional por fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'id'             => 'id',
            'created_at'     => 'created_at',
            'user'           => 'user_id',
            'event'          => 'event',
            'auditable_type' => 'auditable_type',
            'ip_address'     => 'ip_address',
        ];

        $sort = $request->get('sort', 'id');
        $direction = strtolower($request->get('direction', 'desc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query->orderBy($sortableColumns[$sort], $direction);
        if ($sort !== 'id') {
            $query->orderBy('id', 'desc');
        }

        // Exportación a Excel / CSV con todos los filtros aplicados
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            $exportQuery = clone $query;
            $filename = 'auditoria_sistema_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'ID',
                    'Fecha y Hora',
                    'Usuario',
                    'Evento',
                    'Entidad',
                    'ID Registro',
                    'IP Origen',
                ], ';');

                $exportQuery->chunk(1000, function ($chunk) use ($handle) {
                    foreach ($chunk as $a) {
                        fputcsv($handle, [
                            $a->id,
                            $a->created_at ? $a->created_at->format('d/m/Y H:i:s') : '-',
                            $a->user?->name ?? 'Sistema',
                            strtoupper($a->event),
                            class_basename($a->auditable_type),
                            $a->auditable_id,
                            $a->ip_address ?? '-',
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $audits = $query->paginate(20)->withQueryString();

        // Tipos de entidades auditadas registradas en el sistema para el selector
        $entidades = [
            'App\Models\Cliente'      => 'Cliente',
            'App\Models\Credito'      => 'Crédito',
            'App\Models\Cuota'        => 'Cuota',
            'App\Models\Presentacion' => 'Presentación',
        ];

        return view('sistema.auditoria.index', compact('audits', 'entidades', 'sort', 'direction'));
    }

    /**
     * Rollback / Deshacer una actualización de auditoría.
     */
    public function rollback(Audit $audit)
    {
        if ($audit->event !== 'updated') {
            return back()->with('error', 'Solo se pueden revertir eventos de modificación (updated).');
        }

        $auditable = $audit->auditable;

        if (! $auditable) {
            return back()->with('error', 'El registro original ya no existe en la base de datos.');
        }

        if (! method_exists($auditable, 'transitionTo')) {
            return back()->with('error', 'El modelo destino no soporta la transición de estados.');
        }

        try {
            // transitionTo($audit, true) aplica los old_values en el modelo
            $auditable->transitionTo($audit, true);
            $auditable->save();

            return back()->with('success', "Valores anteriores restaurados correctamente para el registro #{$audit->auditable_id}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo restaurar el estado anterior: ' . $e->getMessage());
        }
    }
}
