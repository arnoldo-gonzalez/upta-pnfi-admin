<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProtocolTemplate;
use App\Services\ProtocolDocxService;
use Illuminate\Http\Request;

class ProtocolPreviewController extends Controller
{
    private ProtocolDocxService $docxService;

    public function __construct()
    {
        $this->docxService = new ProtocolDocxService();
    }

    /**
     * GET /protocolo/llenar
     * Muestra el formulario de llenado del protocolo (standalone).
     */
    public function create()
    {
        $template = ProtocolTemplate::active();

        if (! $template) {
            return view('protocol.form', ['noTemplate' => true]);
        }

        return view('protocol.form', compact('template'));
    }

    /**
     * GET /projects/{project}/protocol/fill
     * Muestra el formulario de llenado del protocolo para un proyecto (modo estudiante).
     */
    public function fillProject(Project $project)
    {
        $user = auth()->user();

        $isStudentMember = $project->students()
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if (! $isStudentMember) {
            abort(403, 'No estas autorizado para llenar este protocolo.');
        }

        $template = ProtocolTemplate::active();

        if (! $template) {
            return view('protocol.form', ['noTemplate' => true]);
        }

        // Si el proyecto ya tiene respuestas guardadas, las usamos como base
        // (old() tiene prioridad durante la validación fallida).
        $values = $this->getSavedValues($project, $template);

        return view('protocol.form', compact('template', 'values', 'project'));
    }

    /**
     * POST /protocolo/llenar/preview
     * Previsualiza el docx a partir del schema de la plantilla activa.
     */
    public function preview(Request $request)
    {
        $template = ProtocolTemplate::active();

        if (! $template) {
            return redirect()->route('protocolo.llenar')
                ->with('error', 'No hay plantilla de protocolo configurada.');
        }

        $validated = $request->validate($this->docxService->validationRules($template));

        try {
            $tmp = $this->docxService->generate($template, $validated);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('The protocol could not be generated. Please contact the administrator.'));
        }

        return response()->download($tmp, 'protocolo_previsualizado.docx')
            ->deleteFileAfterSend(true);
    }

    /**
     * POST /projects/{project}/protocol/preview
     * Previsualiza el docx para un proyecto específico usando respuestas
     * guardadas o los valores enviados.
     */
    public function previewProject(Project $project, Request $request)
    {
        $user = auth()->user();

        $isStudentMember = $project->students()
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();

        if (! $isStudentMember) {
            abort(403, 'No estas autorizado para previsualizar este protocolo.');
        }

        $template = ProtocolTemplate::active();

        if (! $template) {
            return redirect()->route('protocolo.llenar')
                ->with('error', 'No hay plantilla de protocolo configurada.');
        }

        $validated = $request->validate($this->docxService->validationRules($template));

        try {
            $tmp = $this->docxService->generate($template, $validated);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('The protocol could not be generated. Please contact the administrator.'));
        }

        return response()->download($tmp, 'protocolo_previsualizado.docx')
            ->deleteFileAfterSend(true);
    }

    /**
     * Obtiene las respuestas guardadas para el proyecto, indexadas por placeholder key.
     */
    private function getSavedValues(Project $project, ProtocolTemplate $template): array
    {
        $document = $project->documents()
            ->whereHas('type', function ($query) {
                $query->where('name', 'Protocolo');
            })
            ->latest()
            ->first();

        if (! $document) {
            return [];
        }

        $answers = $document->answers()->pluck('value', 'placeholder_key')->all();

        $values = [];
        foreach ($template->schema_json ?? [] as $campo) {
            $key = $campo['key'];
            $values[$key] = $answers[$key] ?? null;
        }

        // Si hay correcciones por campo (ancladas a la evaluación del docente), sobreescribimos los valores
        $corrections = \App\Models\ProjectProtocolFieldCorrection::whereHas('projectEvaluation', function ($q) use ($document) {
            $q->where('project_document_id', $document->id);
        })->orderBy('id')->pluck('correction', 'placeholder_key')->all();
        foreach ($corrections as $key => $correction) {
            $values[$key] = $correction;
        }

        return $values;
    }
}
