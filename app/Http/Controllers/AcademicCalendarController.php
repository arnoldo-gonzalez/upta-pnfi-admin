<?php

namespace App\Http\Controllers;

use App\Models\AcademicActivity;
use App\Models\AcademicTerm;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicCalendarController extends Controller
{
    public function index()
    {
        // Traemos los trimestres ordenados por fecha, con sus actividades
        $terms = AcademicTerm::with('activities')->orderBy('start_date', 'desc')->get();
        return view('calendar.index', compact('terms'));
    }

    public function storeTerm(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'], // Ej: Trimestre 1
            'period' => ['required', 'string', 'max:20'], // Ej: 2025-2026
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $overlaps = AcademicTerm::where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($overlaps) {
            return back()->withInput()->withErrors([
                'start_date' => __('This term overlaps with an existing term.'),
            ]);
        }

        $term = AcademicTerm::create($validated);

        AuditLog::record('create_term', 'Creó el trimestre ' . $term->name . ' (' . $term->period . ').');

        return redirect()->route('calendar.index')->with('success', __('Term created successfully.'));
    }

    public function destroyTerm(AcademicTerm $term)
    {
        $name = $term->name;
        $term->delete(); // Las actividades se borran en cascada por la migración

        AuditLog::record('delete_term', 'Eliminó el trimestre ' . $name . '.');

        return redirect()->route('calendar.index')->with('success', __('Term deleted successfully.'));
    }

    public function storeActivity(Request $request, AcademicTerm $term)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['protocol_submission', 'tutor_request'])],
            'start_date' => ['required', 'date', 'after_or_equal:' . $term->start_date->toDateString()],
            'end_date' => ['required', 'date', 'after:start_date', 'before_or_equal:' . $term->end_date->toDateString()],
        ]);

        if ($request->date('start_date') < $term->start_date || $request->date('end_date') > $term->end_date) {
            return back()->withInput()->withErrors([
                'start_date' => __('The activity must be inside the term dates.'),
            ]);
        }

        // Reemplaza la ventana anterior del mismo tipo dentro del trimestre.
        $term->activities()->where('type', $validated['type'])->delete();
        $activity = $term->activities()->create($validated);

        AuditLog::record('create_activity', 'Creó la actividad ' . $activity->name . ' del trimestre ' . $term->name . '.');

        return redirect()->route('calendar.index')->with('success', __('Activity added successfully.'));
    }

    public function destroyActivity(AcademicActivity $activity)
    {
        $name = $activity->name;
        $activity->delete();

        AuditLog::record('delete_activity', 'Eliminó la actividad ' . $name . '.');

        return redirect()->route('calendar.index')->with('success', __('Activity deleted successfully.'));
    }
}