<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectEvaluationScore;
use App\Models\EvaluationCriteria;
use App\Models\AuditLog;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorEvaluationController extends Controller
{
    public function create(Project $project)
    {
        // Seguridad: Solo el tutor asignado y con proyecto ACTIVE
        if ($project->tutor_id !== auth()->id() || $project->status_id !== UserStatus::ACTIVE->value) {
            abort(403, 'No tienes permiso para evaluar este proyecto.');
        }

        // Cargamos los criterios del eje temático.
        $criteria = $project->investigationLine?->thematicAxis?->evaluationCriteria ?? collect();

        if ($criteria->isEmpty()) {
            return back()->with('error', __('No evaluation criteria defined for this thematic axis. Please contact the administrator.'));
        }

        $writtenCriteria = $criteria->filter(function ($c) {
            return $c->type?->name === 'Informe Escrito';
        });

        $technicalCriteria = $criteria->filter(function ($c) {
            return $c->type?->name === 'Desarrollo Técnico';
        });

        return view('tutors.evaluations.checklist', compact('project', 'writtenCriteria', 'technicalCriteria'));
    }

    public function store(Request $request, Project $project)
    {
        if ($project->tutor_id !== auth()->id() || $project->status_id !== UserStatus::ACTIVE->value) {
            abort(403);
        }

        $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $criteria = $project->investigationLine?->thematicAxis?->evaluationCriteria ?? collect();

        if ($criteria->isEmpty()) {
            return back()->with('error', __('No evaluation criteria defined for this thematic axis.'));
        }

        $criteriaById = $criteria->keyBy('id');

        // Solo se aceptan los criterios del eje del proyecto.
        $allowedIds = $criteriaById->keys()->map(fn ($id) => (string) $id)->all();
        $requestedIds = array_map('strval', array_keys($request->input('scores', [])));

        if (array_diff($requestedIds, $allowedIds) !== []) {
            return back()->with('error', __('The submitted evaluation contains criteria that do not belong to this project.'));
        }

        if (array_diff($allowedIds, $requestedIds) !== []) {
            return back()->with('error', __('You must score every evaluation criterion.'));
        }

        // Pesos configurados por tipo (se calculan sobre todos los criterios, no
        // solo sobre los enviados, para que un subconjunto no infle la nota).
        $writtenWeight = 0;
        $technicalWeight = 0;

        foreach ($criteria as $criterion) {
            $typeName = $criterion->type?->name;

            if ($typeName === 'Informe Escrito') {
                $writtenWeight += (int) $criterion->weight_percentage;
            } elseif ($typeName === 'Desarrollo Técnico') {
                $technicalWeight += (int) $criterion->weight_percentage;
            } else {
                return back()->with('error', __('The thematic axis has an invalid evaluation criterion type.'));
            }
        }

        if ($writtenWeight <= 0 || $technicalWeight <= 0) {
            return back()->with('error', __('The evaluation criteria weights are not configured correctly.'));
        }

        DB::transaction(function () use ($request, $project, $criteriaById, $writtenWeight, $technicalWeight) {
            $writtenTotal = 0;
            $technicalTotal = 0;

            foreach ($request->scores as $criteriaId => $score) {
                $criterion = $criteriaById[(int) $criteriaId];

                ProjectEvaluationScore::updateOrCreate(
                    ['project_id' => $project->id, 'evaluation_criteria_id' => $criterion->id],
                    ['score' => $score]
                );

                $weightedScore = ($score * $criterion->weight_percentage) / 100;

                if ($criterion->type?->name === 'Informe Escrito') {
                    $writtenTotal += $weightedScore;
                } else {
                    $technicalTotal += $weightedScore;
                }
            }

            // Normalizar a base 100 por si el administrador no sumó exactamente 100 %.
            $finalWritten = ($writtenTotal / $writtenWeight) * 100;
            $finalTechnical = ($technicalTotal / $technicalWeight) * 100;

            // Umbrales
            $isTrayecto3 = $project->section->trayecto->number == 3;
            $minTechnical = $isTrayecto3 ? 95 : 80;
            $minWritten = 80;

            $approved = ($finalWritten >= $minWritten) && ($finalTechnical >= $minTechnical);

            $project->update([
                'written_score' => round($finalWritten, 2),
                'technical_score' => round($finalTechnical, 2),
                'tutor_evaluation_status_id' => $approved ? UserStatus::ACTIVE->value : UserStatus::REJECTED->value,
                'status_id' => $approved ? UserStatus::READY_FOR_EXPERTS->value : UserStatus::NEEDS_CORRECTIONS->value,
            ]);
        });

        AuditLog::record(
            'tutor_evaluation',
            'Registró la evaluación del tutor para el proyecto: ' . $project->title
        );

        return redirect()->route('tutors.index')->with('success', __('Project evaluated successfully.'));
    }
}