<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\LateSubmissionRequest;
use Illuminate\Http\Request;

class LateSubmissionController extends Controller
{
    // 1. Estudiante envía la solicitud
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:20']
        ]);

        $hasPendingRequest = LateSubmissionRequest::where('project_id', $project->id)
            ->where('status_id', UserStatus::PENDING->value)
        ->exists();

        if ($hasPendingRequest) {
            return back()->with('error', __('You already have a pending request under review. Please wait for the coordination to respond.'));
        }

        LateSubmissionRequest::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'status_id' => UserStatus::PENDING->value,
        ]);

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
            'decision' => ['required', 'in:approved,rejected']
        ]);

        $newStatus = $request->decision === 'approved' 
            ? UserStatus::ACTIVE->value 
            : UserStatus::REJECTED->value;

        $lateRequest->update([
            'status_id' => $newStatus,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        if ($request->decision === 'approved') {
            $lateRequest->project->update(['allow_late_submission' => true]);
        }

        return back()->with('success', __('Request reviewed successfully.'));
    }
}