<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Project;
use App\Notifications\EventScheduled;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['type', 'project'])->orderBy('start_date', 'desc')->paginate(10);
        return view('events.index', compact('events'));
    }

    public function create()
    {
        $event = null;

        return view('events.create', $this->formData($event));
    }

    public function edit(Event $event)
    {
        return view('events.edit', $this->formData($event));
    }

    public function store(Request $request)
    {
        $validated = $this->validateEvent($request);

        $event = Event::create($validated);

        \App\Models\AuditLog::record('create_event', 'Creó el evento: ' . $event->title);

        $this->notifyEvent($event);

        return redirect()->route('events.index')->with('success', __('Event created successfully.'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $this->validateEvent($request, allowPast: true);

        $event->update($validated);

        \App\Models\AuditLog::record('update_event', 'Editó el evento: ' . $event->title);

        return redirect()->route('events.index')->with('success', __('Event updated successfully.'));
    }

    private function formData(?Event $event): array
    {
        $eventTypes = EventType::all();
        $projects = Project::orderBy('title')->get(['id', 'title']);

        // Lista blanca de rutas válidas por rol
        $availableRoutes = [
            'coordinador' => [
                'coordinator.protocols' => 'Protocols Report',
                'coordinator.tutorships' => 'Tutorships Report',
                'evaluations.coordinator.index' => 'Evaluator Assignment',
            ],
            'docente' => [
                'evaluations.teacher.index' => 'My Pending Evaluations',
                'tutors.index' => 'Tutoring Requests',
                'tutor-logs.teacher' => 'Logbooks Review',
            ],
            'estudiante' => [
                'projects.index' => 'My Project',
                'tutors.student.index' => 'My Academic Tutor',
            ],
        ];

        // Excluimos 'admin' porque el admin tiene acceso a todo por defecto
        $roles = ['coordinador', 'docente', 'estudiante'];

        return compact('eventTypes', 'roles', 'availableRoutes', 'event', 'projects');
    }

    private function validateEvent(Request $request, bool $allowPast = false): array
    {
        $dateRules = ['required', 'date'];

        if (! $allowPast) {
            $dateRules[] = 'after_or_equal:today';
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => $dateRules,
            'location' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'visible_to_roles' => ['required', 'array', 'min:1'],
            'visible_to_roles.*' => ['in:admin,coordinador,docente,estudiante'],
            'role_links' => ['nullable', 'array'],
            'role_links.*' => ['nullable', 'string', 'in:coordinator.protocols,coordinator.tutorships,evaluations.coordinator.index,evaluations.teacher.index,tutors.index,tutor-logs.teacher,projects.index,tutors.student.index'],
        ]);

        // Limpiamos los role_links para quitar los vacíos
        $roleLinks = array_filter($request->input('role_links', []));

        $validated['role_links'] = empty($roleLinks) ? null : $roleLinks;

        return $validated;
    }

    public function cartelera()
    {
        $user = auth()->user();
        $roles = $user->getRoleNames()->all();

        $events = Event::with(['type', 'project'])
            ->where('start_date', '>=', now()->startOfDay())
            ->orderBy('start_date')
            ->get();

        if (! $user->hasRole('admin')) {
            $events = $events
                ->filter(fn (Event $event) => count(array_intersect($roles, $event->visible_to_roles ?? [])) > 0)
                ->values();
        }

        return view('events.cartelera', compact('events'));
    }

    private function notifyEvent(Event $event): void
    {
        $project = $event->project;

        if (! $project) {
            return;
        }

        $users = collect();

        foreach ($project->students()->with('enrollment.student')->get() as $projectStudent) {
            if ($projectStudent->enrollment?->student) {
                $users->push($projectStudent->enrollment->student);
            }
        }

        if ($project->tutor) {
            $users->push($project->tutor);
        }

        foreach ($project->evaluations()->with('teacher')->get() as $evaluation) {
            if ($evaluation->teacher) {
                $users->push($evaluation->teacher);
            }
        }

        $users->unique('id')->each(fn ($user) => $user->notify(new EventScheduled($event)));
    }

    public function destroy(Event $event)
    {
        $title = $event->title;
        $event->delete();

        \App\Models\AuditLog::record('delete_event', 'Eliminó el evento: ' . $title);

        return redirect()->route('events.index')->with('success', __('Event deleted successfully.'));
    }
}