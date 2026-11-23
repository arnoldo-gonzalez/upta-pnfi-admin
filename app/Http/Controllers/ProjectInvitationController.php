<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\ProjectInvitation;
use App\Models\ProjectStudent;
use App\Models\Project;
use App\Models\Enrollment;
use App\Models\Status;
use App\Notifications\InvitationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectInvitationController extends Controller
{
    // Enviar una invitación
    public function store(Request $request)
    {
        $request->validate([
            'receiver_enrollment_id' => ['required', 'exists:enrollments,id'],
        ]);

        $senderEnrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if (! $senderEnrollment) {
            abort(403, 'No tienes una inscripción activa.');
        }

        $projectStudent = ProjectStudent::where('enrollment_id', $senderEnrollment->id)->first();

        if (!$projectStudent) {
            return back()->with('error', __('You do not have an active project.'));
        }

        $project = $projectStudent->project;

        if ($project->status_id !== UserStatus::DRAFT->value) {
            return back()->with('error', __('You cannot invite members to a project that is already in review.'));
        }

        if (!$project->section->is_active) {
            return back()->with('error', __('You cannot invite members because the academic period is closed.'));
        }

        // 1. Contar miembros actuales
        $currentMembers = ProjectStudent::where('project_id', $project->id)->count();
        
        // 2. Contar invitaciones pendientes enviadas
        $pendingInvitations = ProjectInvitation::where('project_id', $project->id)
            ->where('status_id', UserStatus::PENDING->value)
        ->count();

        // 3. La suma no debe superar el límite
        if (($currentMembers + $pendingInvitations) >= $project->section->max_team_size) {
            return back()->with('error', __('The team has reached the maximum limit of members and pending invitations for this section.'));
        }

        if ($senderEnrollment->id == $request->receiver_enrollment_id) {
            return back()->with('error', __('You cannot invite yourself.'));
        }

        $receiverEnrollment = Enrollment::with('student')
            ->whereKey($request->receiver_enrollment_id)
            ->where('is_active', true)
            ->where('section_id', $project->section_id)
            ->first();

        if (! $receiverEnrollment) {
            return back()->with('error', __('The selected student is not active in your section.'));
        }

        $alreadyInTeam = ProjectStudent::where('project_id', $project->id)
            ->where('enrollment_id', $receiverEnrollment->id)
            ->exists();

        if ($alreadyInTeam) {
            return back()->with('error', __('This student is already in your team.'));
        }

        $alreadyInAnotherProject = ProjectStudent::where('enrollment_id', $receiverEnrollment->id)->exists();

        if ($alreadyInAnotherProject) {
            return back()->with('error', __('This student already belongs to another project.'));
        }

        // updateOrCreate permite reenviar una invitación que había sido rechazada.
        $invitation = ProjectInvitation::updateOrCreate(
            [
                'project_id' => $project->id,
                'receiver_enrollment_id' => $receiverEnrollment->id,
            ],
            [
                'sender_enrollment_id' => $senderEnrollment->id,
                'status_id' => UserStatus::PENDING->value,
            ]
        );

        $receiverEnrollment->student?->notify(new InvitationReceived($project->title, auth()->user()->name));

        AuditLog::record('invite_student', 'Invitó a ' . $receiverEnrollment->student?->name . ' al proyecto: ' . $project->title);

        return back()->with('success', __('Invitation sent successfully.'));
    }

    // Aceptar una invitación
    public function accept(ProjectInvitation $invitation)
    {
        $enrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if (! $enrollment) {
            abort(403, 'No tienes una inscripción activa.');
        }

        if ($invitation->receiver_enrollment_id !== $enrollment->id) {
            abort(403);
        }

        $project = $invitation->project;

        if ($invitation->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('This invitation is no longer available.'));
        }

        if (!$project->section->is_active) {
            return back()->with('error', __('You cannot accept this invitation because the academic period is closed.'));
        }

        if (ProjectStudent::where('enrollment_id', $enrollment->id)->exists()) {
            return back()->with('error', __('You already belong to a project.'));
        }

        try {
            DB::transaction(function () use ($invitation, $project, $enrollment) {
                $lockedInvitation = ProjectInvitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();

                if ($lockedInvitation->status_id !== UserStatus::PENDING->value) {
                    throw new \RuntimeException('Esta invitación ya no está disponible.');
                }

                if (ProjectStudent::where('enrollment_id', $enrollment->id)->exists()) {
                    throw new \RuntimeException('Ya perteneces a un proyecto.');
                }

                $currentMembers = ProjectStudent::where('project_id', $project->id)->count();

                if ($currentMembers >= $project->section->max_team_size) {
                    throw new \RuntimeException('El equipo alcanzó el tamaño máximo.');
                }

                $lockedInvitation->update(['status_id' => UserStatus::ACTIVE->value]);

                ProjectStudent::firstOrCreate([
                    'project_id' => $project->id,
                    'enrollment_id' => $enrollment->id,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditLog::record('accept_invitation', 'Se unió al proyecto: ' . $project->title);

        return back()->with('success', __('You have joined the team.'));
    }

    // Rechazar una invitación
    public function reject(ProjectInvitation $invitation)
    {
        $enrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if (! $enrollment) {
            abort(403, 'No tienes una inscripción activa.');
        }

        if ($invitation->receiver_enrollment_id !== $enrollment->id) {
            abort(403);
        }

        if ($invitation->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('This invitation is no longer available.'));
        }

        $invitation->update(['status_id' => UserStatus::REJECTED->value]);

        AuditLog::record('reject_invitation', 'Rechazó la invitación al proyecto: ' . $invitation->project->title);

        return back()->with('success', __('Invitation rejected.'));
    }
}