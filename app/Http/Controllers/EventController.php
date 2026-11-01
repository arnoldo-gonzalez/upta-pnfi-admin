<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('type')->orderBy('start_date', 'desc')->paginate(10);
        return view('events.index', compact('events'));
    }

    public function create()
    {
        $eventTypes = EventType::all();
        
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
            ]
        ];
        
        // Excluimos 'admin' porque el admin tiene acceso a todo por defecto
        $roles = ['coordinador', 'docente', 'estudiante'];
        
        return view('events.create', compact('eventTypes', 'roles', 'availableRoutes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'visible_to_roles' => ['required', 'array', 'min:1'],
            'visible_to_roles.*' => ['in:admin,coordinador,docente,estudiante'],
        ]);

        // Limpiamos los role_links para quitar los vacíos
        $roleLinks = array_filter($request->input('role_links', []));

        Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'event_type_id' => $request->event_type_id,
            'visible_to_roles' => $request->visible_to_roles,
            'role_links' => empty($roleLinks) ? null : $roleLinks,
        ]);

        return redirect()->route('events.index')->with('success', __('Event created successfully.'));
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', __('Event deleted successfully.'));
    }
}