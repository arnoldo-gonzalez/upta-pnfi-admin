<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\ProjectInvitation;
use App\Models\ProjectStudent;
use App\Models\Project;
use App\Models\Enrollment;
use App\Models\Status;
use Illuminate\Http\Request;

class ProjectInvitationController extends Controller
{
    // Enviar una invitación
    public function store(Request $request)
    {
        $request->validate([
            'receiver_enrollment_id' => ['required', 'exists:enrollments,id'],
        ]);

        $senderEnrollment = auth()->user()->enrollments()->where('is_active', true)->first();
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
        
        $alreadyInTeam = ProjectStudent::where('project_id', $project->id)
            ->where('enrollment_id', $request->receiver_enrollment_id)
        ->exists();
        if ($alreadyInTeam) {
            return back()->with('error', __('This student is already in your team.'));
        }

        ProjectInvitation::firstOrCreate(
            [
                'project_id' => $project->id, 
                'receiver_enrollment_id' => $request->receiver_enrollment_id
            ],
            [
                'sender_enrollment_id' => $senderEnrollment->id,
                'status_id' => UserStatus::PENDING->value,
            ]
        );

        return back()->with('success', __('Invitation sent successfully.'));
    }

    // Aceptar una invitación
    public function accept(ProjectInvitation $invitation)
    {
        $enrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if ($invitation->receiver_enrollment_id !== $enrollment->id) {
            abort(403);
        }

        $project = $invitation->project;

        $currentMembers = ProjectStudent::where('project_id', $project->id)->count();
        if ($currentMembers >= $project->section->max_team_size) {
            return back()->with('error', __('The team has reached the maximum size. You cannot accept this invitation.'));
        }

        if (!$project->section->is_active) {
            return back()->with('error', __('You cannot accept this invitation because the academic period is closed.'));
        }

        $invitation->update(['status_id' => UserStatus::ACTIVE->value]);

        // Agregar al equipo
        ProjectStudent::create([
            'project_id' => $project->id,
            'enrollment_id' => $enrollment->id,
        ]);

        return back()->with('success', __('You have joined the team.'));
    }

    // Rechazar una invitación
    public function reject(ProjectInvitation $invitation)
    {
        $enrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if ($invitation->receiver_enrollment_id !== $enrollment->id) {
            abort(403);
        }

        // Cambiar estado a RECHAZADO (ID 3)
        $invitation->update(['status_id' => UserStatus::REJECTED->value]);

        return back()->with('success', __('Invitation rejected.'));
    }
}