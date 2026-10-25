<?php

namespace App\Http\Controllers;

use App\Enums\TutorRequestStatus;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\ProjectStudent;
use App\Models\InvestigationLine;
use App\Models\ProjectInvitation;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index() {
        $enrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if (!$enrollment) {
            return redirect()->route('dashboard')->with('error', __('You do not have an active enrollment.'));
        }

        $projectStudent = ProjectStudent::where('enrollment_id', $enrollment->id)->first();
        $project = $projectStudent ? $projectStudent->project : null;

        $teamMembers = collect();
        $receivedInvitations = collect();
        $availableStudents = collect();
        $sentInvitations = collect();

        $receivedInvitations = ProjectInvitation::with(['project', 'sender.student'])
            ->where('receiver_enrollment_id', $enrollment->id)
            ->where('status_id', UserStatus::PENDING->value)
        ->get();
        
        if ($project) {
            $teamMembers = ProjectStudent::with('enrollment.student')
                ->where('project_id', $project->id)
            ->get();

            $sentInvitations = ProjectInvitation::with(['receiver.student', 'status'])
                ->where('project_id', $project->id)
                ->orderBy('created_at', 'desc')
            ->get();

            if ($project->status_id === 4) {
                $availableStudents = Enrollment::with('student')
                    ->where('section_id', $enrollment->section_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $enrollment->id)
                    ->whereHas('student', function($query) {
                        $query->where('status_id', UserStatus::ACTIVE->value);
                    })
                    ->whereDoesntHave('projectStudents') 
                    ->whereDoesntHave('receivedInvitations', function($query) use ($project) {
                        $query->where('project_id', $project->id); 
                    })
                ->get();
            }
        }

        $studentsJson = $availableStudents->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'name' => $enrollment->student->name,
                'credential' => (string) $enrollment->student->credential,
            ];
        });

        return view('projects.index', compact('project', 'teamMembers', 'sentInvitations', 'receivedInvitations', 'enrollment', 'availableStudents', 'studentsJson'));
    }

    public function create()
    {
        $investigationLines = InvestigationLine::all();
        return view('projects.create', compact('investigationLines'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'investigation_line_id' => ['required', 'exists:investigation_lines,id'],
            'justification' => ['required', 'string'],
        ]);

        $enrollment = auth()->user()->enrollments()->where('is_active', true)->first();

        if (!$enrollment) {
            return back()->with('error', __('You do not have an active enrollment.'));
        }

        DB::transaction(function () use ($request, $enrollment) {
            
            $activeTeacher = $enrollment->section->activeTeacher->first();

            $project = Project::create([
                'title' => $request->title,
                'investigation_line_id' => $request->investigation_line_id,
                'justification' => $request->justification,
                'status_id' => UserStatus::DRAFT->value,
                'section_id' => $enrollment->section_id,
                'section_teacher_id' => $activeTeacher ? $activeTeacher->pivot->id : null,
                'tutor_request_status_id' => TutorRequestStatus::NO_REQUEST->value,
            ]);

            ProjectStudent::create([
                'project_id' => $project->id,
                'enrollment_id' => $enrollment->id,
            ]);
        });

        return redirect()->route('projects.index')->with('success', __('Project created. Now invite your team members.'));
    }
}