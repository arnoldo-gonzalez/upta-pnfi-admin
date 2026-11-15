<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectEvaluationScore;
use App\Models\EvaluationCriteria;
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

        // Cargamos los criterios del eje temático. Usamos collect() por si acaso viene null.
        $criteria = $project->investigationLine->thematicAxis->evaluationCriteria ?? collect();

        if ($criteria->isEmpty()) {
            return back()->with('error', __('No evaluation criteria defined for this thematic axis. Please contact the administrator.'));
        }

        $writtenCriteria = $criteria->filter(function($c) {
            return $c->type && $c->type->name === 'Informe Escrito';
        });
        
        $technicalCriteria = $criteria->filter(function($c) {
            return $c->type && $c->type->name === 'Desarrollo Técnico';
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

        DB::transaction(function () use ($request, $project) {
            
            $writtenTotal = 0;
            $technicalTotal = 0;
            $writtenWeight = 0;
            $technicalWeight = 0;

            foreach ($request->scores as $criteriaId => $score) {
                $criteria = EvaluationCriteria::find($criteriaId);

                ProjectEvaluationScore::updateOrCreate(
                    ['project_id' => $project->id, 'evaluation_criteria_id' => $criteriaId],
                    ['score' => $score]
                );

                // Calcular ponderación
                $weightedScore = ($score * $criteria->weight_percentage) / 100;

                if ($criteria->type->name === 'Informe Escrito') {
                    $writtenTotal += $weightedScore;
                    $writtenWeight += $criteria->weight_percentage;
                } else {
                    $technicalTotal += $weightedScore;
                    $technicalWeight += $criteria->weight_percentage;
                }
            }

            // Normalizar a base 100 por si el admin no sumó exactamente 100% en los pesos
            $finalWritten = ($writtenTotal / $writtenWeight) * 100;
            $finalTechnical = ($technicalTotal / $technicalWeight) * 100;

            // Umbrales
            $isTrayecto3 = $project->section->trayecto->number == 3;
            $minTechnical = $isTrayecto3 ? 95 : 80;
            $minWritten = 80;

            $approved = ($finalWritten >= $minWritten) && ($finalTechnical >= $minTechnical);

            if ($approved) {
                $project->update([
                    'written_score' => round($finalWritten, 2),
                    'technical_score' => round($finalTechnical, 2),
                    'tutor_evaluation_status_id' => UserStatus::ACTIVE->value,
                    'status_id' => UserStatus::READY_FOR_EXPERTS->value
                ]);
            } else {
                $project->update([
                    'written_score' => round($finalWritten, 2),
                    'technical_score' => round($finalTechnical, 2),
                    'tutor_evaluation_status_id' => UserStatus::REJECTED->value,
                    'status_id' => UserStatus::NEEDS_CORRECTIONS->value
                ]);
            }
        });

        return redirect()->route('tutors.index')->with('success', __('Project evaluated successfully.'));
    }
}