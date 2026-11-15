<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\AcademicActivity;
use Illuminate\Http\Request;

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
        $request->validate([
            'name' => ['required', 'string', 'max:255'], // Ej: Trimestre 1
            'period' => ['required', 'string', 'max:20'], // Ej: 2025-2026
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        AcademicTerm::create($request->all());

        return redirect()->route('calendar.index')->with('success', __('Term created successfully.'));
    }

    public function destroyTerm(AcademicTerm $term)
    {
        $term->delete(); // Las actividades se borran en cascada por la migración
        return redirect()->route('calendar.index')->with('success', __('Term deleted successfully.'));
    }

    public function storeActivity(Request $request, AcademicTerm $term)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'], // Aquí van los tipos exactos que validamos en el código
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $term->activities()->create($request->all());

        return redirect()->route('calendar.index')->with('success', __('Activity added successfully.'));
    }

    public function destroyActivity(AcademicActivity $activity)
    {
        $activity->delete();
        return redirect()->route('calendar.index')->with('success', __('Activity deleted successfully.'));
    }
}