<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\AcademicActivity;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\LateSubmissionRequest;
use App\Notifications\LateSubmissionReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LateSubmissionController extends Controller
{
    // 1. Estudiante envía la solicitud
    public function store(Request $request, Project $project)
    {
        $isMember = $project->students()
            ->whereHas('enrollment', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->exists();

        if (! $isMember) {
            abort(403, 'No tienes permiso para solicitar una prórroga de este proyecto.');
        }

        if (AcademicActivity::isOpen('protocol_submission')) {
            return back()->with('error', __('The submission period is still open; a late request is not needed.'));
        }

        if (! in_array($project->status_id, [UserStatus::DRAFT->value, UserStatus::NEEDS_CORRECTIONS->value], true)) {
            return back()->with('error', __('This project cannot request a late submission at this stage.'));
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:20'],
        ]);

        $hasPendingRequest = LateSubmissionRequest::where('project_id', $project->id)
            ->where('status_id', UserStatus::PENDING->value)
            ->exists();

        // Un permiso ya consumido (flag en false) no impide pedir uno nuevo.
        if ($hasPendingRequest || $project->allow_late_submission) {
            return back()->with('error', __('You already have a pending or unused approved request for this project.'));
        }

        LateSubmissionRequest::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'status_id' => UserStatus::PENDING->value,
        ]);

        AuditLog::record('request_late_submission', 'Solicitó prórroga para el proyecto: ' . $project->title);

        return back()->with('success', __('Your request for late submission has been sent to the coordination.'));
    }

    public function index()
    {
        $requests = LateSubmissionRequest::with(['project', 'user'])
            ->where('status_id', UserStatus::PENDING->value)
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('coordinator.late-requests', compact('requests'));
    }

    public function review(Request $request, LateSubmissionRequest $lateRequest)
    {
        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        if ($lateRequest->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('This request has already been reviewed.'));
        }

        $newStatus = $request->decision === 'approved'
            ? UserStatus::ACTIVE->value
            : UserStatus::REJECTED->value;

        DB::transaction(function () use ($lateRequest, $request, $newStatus) {
            $locked = LateSubmissionRequest::whereKey($lateRequest->id)->lockForUpdate()->firstOrFail();

            if ($locked->status_id !== UserStatus::PENDING->value) {
                abort(409, 'This request has already been reviewed.');
            }

            $locked->update([
                'status_id' => $newStatus,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            if ($request->decision === 'approved') {
                $locked->project->update(['allow_late_submission' => true]);
            }
        });

        AuditLog::record(
            'review_late_submission',
            ($request->decision === 'approved' ? 'Aprobó' : 'Rechazó') . ' la prórroga del proyecto: ' . $lateRequest->project->title
        );

        $lateRequest->user?->notify(new LateSubmissionReviewed(
            'Solicitud de prórroga revisada',
            'Tu solicitud para el proyecto "' . $lateRequest->project->title . '" fue '
                . ($request->decision === 'approved' ? 'aprobada. Ya puedes enviar tu protocolo.' : 'rechazada.')
        ));

        return back()->with('success', __('Request reviewed successfully.'));
    }
}