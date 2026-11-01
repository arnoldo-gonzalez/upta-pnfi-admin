<?php

namespace App\Policies;

use App\Models\ProjectDocument;
use App\Models\User;
use App\Models\ProjectEvaluation;

class ProjectDocumentPolicy
{
    /**
     * Determinar si el usuario puede descargar el documento.
     */
    public function download(User $user, ProjectDocument $document): bool
    {
        // 1. Coordinadores y Admins pueden ver todo
        if ($user->hasRole('admin') || $user->hasRole('coordinador')) {
            return true;
        }

        $project = $document->project;

        // 2. ESTUDIANTES: Solo pueden ver los documentos de SU proyecto
        if ($user->hasRole('estudiante')) {
            $enrollment = $user->enrollments()->where('is_active', true)->first();
            if (!$enrollment) {
                return false;
            }

            // Verificamos si el estudiante pertenece al equipo de este proyecto
            return $project->students()->where('enrollment_id', $enrollment->id)->exists();
        }

        // 3. DOCENTES: Pueden ver si son el Asesor, el Tutor o un Evaluador asignado
        if ($user->hasRole('docente')) {
            
            // A. ¿Es el Tutor asignado?
            if ($project->tutor_id === $user->id) {
                return true;
            }

            // B. ¿Es el Asesor de la sección del proyecto?
            $activeAdvisor = $project->section->activeTeacher->first();
            if ($activeAdvisor && $activeAdvisor->id === $user->id) {
                return true;
            }

            // C. ¿Es un evaluador asignado a este proyecto?
            $isEvaluator = ProjectEvaluation::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($isEvaluator) {
                return true;
            }
        }

        // Si no cumple ninguna condición, se le deniega el acceso
        return false;
    }
}