<?php

namespace App\Http\Controllers;

use App\Models\ProjectDocument;
use Illuminate\Support\Facades\Gate;
use ZipArchive;

class ProjectDocumentController extends Controller
{
    public function download(ProjectDocument $document) {
        
        // Si el usuario no pasa la validación de la Policy, Laravel lanzará un error 403 automáticamente.
        Gate::authorize('download', $document);

        $zipPath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($zipPath)) {
            abort(404, 'File not found.');
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $fileName = $zip->getNameIndex(0);
            $content = $zip->getFromIndex(0);
            $zip->close();

            return response($content, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
        }

        abort(500, 'Could not read the file.');
    }
}