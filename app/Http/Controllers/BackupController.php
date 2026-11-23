<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index(BackupService $backups)
    {
        return view('admin.backups.index', [
            'backups' => $backups->files(),
        ]);
    }

    public function store(BackupService $backups): RedirectResponse
    {
        try {
            $path = $backups->create();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('No se pudo generar el respaldo: :message', ['message' => $e->getMessage()]));
        }

        AuditLog::record('create_backup', 'Generó un respaldo de la base de datos: ' . basename($path));

        return back()->with('success', __('Respaldo generado correctamente: :file', ['file' => basename($path)]));
    }

    public function download(Request $request, string $file)
    {
        $file = basename($file);
        $path = 'backups/' . $file;

        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->download(Storage::disk('local')->path($path), $file);
    }
}
