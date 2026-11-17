<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Muestra la interfaz del panel de copias de seguridad.
     * Accesible por cualquier usuario autenticado.
     */
    public function index()
    {
        $user = auth()->user();
        $canRestore = (bool)($user->is_admin || $user->username === 'superuser');
        $backups = $this->backupService->getBackupsList();
        $databaseName = config('database.connections.' . config('database.default') . '.database');

        return view('sistema.backup.index', compact('backups', 'canRestore', 'databaseName'));
    }

    /**
     * Genera una nueva copia de seguridad y la descarga automáticamente en formato ZIP.
     * Accesible por cualquier usuario autenticado.
     */
    public function generate(Request $request)
    {
        try {
            $backup = $this->backupService->createBackup();

            // Si se solicita descarga inmediata en el navegador
            if ($request->boolean('download', true)) {
                return response()->download($backup['path'], $backup['filename'], [
                    'Content-Type' => 'application/zip',
                ]);
            }

            return redirect()->route('sistema.backup.index')->with('success', "Copia de seguridad '{$backup['filename']}' ({$backup['size_human']}) generada exitosamente.");
        } catch (Exception $e) {
            return redirect()->route('sistema.backup.index')->with('error', "Error al generar la copia de seguridad: " . $e->getMessage());
        }
    }

    /**
     * Descarga un archivo de respaldo ZIP existente del almacenamiento.
     * Accesible por cualquier usuario autenticado.
     */
    public function download(string $filename): BinaryFileResponse
    {
        $cleanName = basename($filename);
        $filePath = $this->backupService->getBackupDirectory() . '/' . $cleanName;

        if (!File::exists($filePath) || !str_ends_with(strtolower($cleanName), '.zip')) {
            abort(404, 'El archivo de copia de seguridad solicitado no existe.');
        }

        return response()->download($filePath, $cleanName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Restaura la base de datos desde un archivo .zip o .sql subido.
     * PRIVILEGIO RESTRINGIDO: Solo Admin o Superuser.
     * Realiza obligatoriamente una copia de seguridad previa antes de sobrescribir.
     */
    public function restore(Request $request)
    {
        $user = auth()->user();
        if (!$user->is_admin && $user->username !== 'superuser') {
            abort(403, 'Acceso denegado: Solo los administradores o el superusuario pueden restaurar la base de datos.');
        }

        $request->validate([
            'backup_file'     => 'required|file|max:524288', // Hasta 512 MB
            'confirm_restore' => 'accepted',
        ], [
            'backup_file.required'     => 'Debe seleccionar un archivo de copia de seguridad (.zip o .sql).',
            'backup_file.max'          => 'El archivo seleccionado excede el límite máximo permitido (512 MB).',
            'confirm_restore.accepted' => 'Debe confirmar que comprende el impacto de la restauración marcando la casilla de verificación.',
        ]);

        $file = $request->file('backup_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['zip', 'sql'])) {
            return back()->with('error', 'Formato no soportado. Debe seleccionar un archivo con extensión .zip o .sql.');
        }

        try {
            $result = $this->backupService->restoreDatabase($file->getRealPath(), $file->getClientOriginalName());

            return redirect()->route('sistema.backup.index')->with('success', "¡Base de datos restaurada exitosamente! Se procesó el archivo '{$result['restored_file']}'. Como medida de seguridad, se creó automáticamente el resguardo previo '{$result['safety_backup']}'.");
        } catch (Exception $e) {
            return back()->with('error', "Error durante la restauración de la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Elimina una copia de seguridad almacenada.
     * PRIVILEGIO RESTRINGIDO: Solo Admin o Superuser.
     */
    public function destroy(string $filename)
    {
        $user = auth()->user();
        if (!$user->is_admin && $user->username !== 'superuser') {
            abort(403, 'Acceso denegado: Solo los administradores o el superusuario pueden eliminar copias de seguridad.');
        }

        if ($this->backupService->deleteBackup($filename)) {
            return redirect()->route('sistema.backup.index')->with('success', "Copia de seguridad '{$filename}' eliminada correctamente.");
        }

        return redirect()->route('sistema.backup.index')->with('error', "No se pudo eliminar el archivo '{$filename}'.");
    }
}
