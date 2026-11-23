<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Section;
use App\Models\Trayecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    /**
     * Display a listing of the sections.
     */
    public function index()
    {
        $sections = Section::with(['trayecto', 'activeTeacher'])->orderBy('trayecto_id')->paginate(10);
        
        return view('sections.index', compact('sections'));
    }

    public function create()
    {
        $trayectos = Trayecto::orderBy('number')->get();
        $teachers = User::role('docente')
            ->where('status_id', \App\Enums\UserStatus::ACTIVE->value)
            ->orderBy('name')
            ->get();

        return view('sections.create', compact('trayectos', 'teachers'));
    }

    public function edit(Section $section)
    {
        $trayectos = Trayecto::orderBy('number')->get();
        $teachers = User::role('docente')
            ->where('status_id', \App\Enums\UserStatus::ACTIVE->value)
            ->orderBy('name')
            ->get();

        $activeTeacher = $section->teachers()->wherePivot('is_active', true)->first();
        $activeTeacherId = $activeTeacher ? $activeTeacher->id : null;
        
        return view('sections.edit', compact('section', 'trayectos', 'teachers', 'activeTeacherId'));
    }

    public function update(Request $request, Section $section)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'max_team_size' => ['required', 'integer', 'min:1'],
            'trayecto_id' => ['required', 'exists:trayectos,id'],
            'period' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'teacher_id' => ['nullable', 'exists:users,id'],
        ]);

        if ($request->filled('teacher_id') && ! User::role('docente')
            ->where('status_id', \App\Enums\UserStatus::ACTIVE->value)
            ->whereKey($request->teacher_id)
            ->exists()) {
            return back()->withInput()->withErrors(['teacher_id' => __('The selected user is not an active teacher.')]);
        }

        $section->update([
            'name' => $request->name,
            'trayecto_id' => $request->trayecto_id,
            'max_team_size' => $request->max_team_size,
            'period' => $request->period,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $section->is_active,
        ]);

        $currentActiveTeacher = $section->teachers()->wherePivot('is_active', true)->first();
        $newTeacherId = $request->filled('teacher_id') ? (int) $request->teacher_id : null;

        // Solo se toca el pivote si el asesor realmente cambió (o se quitó).
        if (! $currentActiveTeacher || $currentActiveTeacher->id !== $newTeacherId) {
            $section->teachers()->updateExistingPivot(
                $section->teachers()->wherePivot('is_active', true)->pluck('users.id')->toArray(),
                ['is_active' => false]
            );

            if ($newTeacherId) {
                $section->teachers()->attach($newTeacherId, ['is_active' => true]);
            }
        }

        AuditLog::record('update_section', 'Editó la sección ' . $section->name . ' del periodo ' . $section->period);

        return redirect()->route('sections.index')->with('success', __('Section updated successfully.'));
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Section $section)
    {
        if ($section->projects()->exists() || $section->enrollments()->exists()) {
            return back()->with('error', __('This section has projects or enrollments and cannot be deleted. Close the period instead.'));
        }

        $name = $section->name;
        $section->delete();

        AuditLog::record('delete_section', 'Eliminó la sección ' . $name);

        return redirect()->route('sections.index')->with('success', __('Section deleted successfully.'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trayecto_id' => ['required', 'exists:trayectos,id'],
            'max_team_size' => ['required', 'integer', 'min:1'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'period' => ['required', 'string', 'max:20'],
        ]);

        if ($request->filled('teacher_id') && ! User::role('docente')
            ->where('status_id', \App\Enums\UserStatus::ACTIVE->value)
            ->whereKey($request->teacher_id)
            ->exists()) {
            return back()->withInput()->withErrors(['teacher_id' => __('The selected user is not an active teacher.')]);
        }

        $section = Section::create([
            'name' => $request->name,
            'trayecto_id' => $request->trayecto_id,
            'max_team_size' => $request->max_team_size,
            'period' => $request->period,
            'is_active' => true,      
        ]);

        AuditLog::record('create_section', 'Creó la sección ' . $section->name . ' para el periodo ' . $section->period);

        if ($request->filled('teacher_id')) {
            $section->teachers()->attach($request->teacher_id, ['is_active' => true]);
        }

        return redirect()->route('sections.index')->with('success', __('Section created successfully.'));
    }

    // Cerrar las secciones activas de un periodo (o todas si no se indica)
    public function closePeriod(Request $request)
    {
        $request->validate([
            'period' => ['nullable', 'string', 'max:20'],
        ]);

        $period = $request->input('period');

        $sections = Section::where('is_active', true)
            ->when($period, fn ($query) => $query->where('period', $period))
            ->get();

        if ($sections->isEmpty()) {
            return back()->with('error', __('There are no active sections to close.'));
        }

        DB::transaction(function () use ($sections) {
            foreach ($sections as $section) {
                $section->update(['is_active' => false]);

                // Cerrar también las inscripciones para que no sigan contando como activas.
                $section->enrollments()
                    ->where('is_active', true)
                    ->update(['is_active' => false, 'completed_at' => now()]);
            }
        });

        AuditLog::record('close_period', 'Cerró ' . $sections->count() . ' sección(es)' . ($period ? ' del periodo ' . $period : '') . '.');

        return redirect()->route('sections.index')->with('success', __('The selected sections and their enrollments have been closed.'));
    }

    // Clonar secciones de un periodo a otro
    public function clonePeriod(Request $request)
    {
        $request->validate([
            'current_period' => ['required', 'string'],
            'new_period' => ['required', 'string', 'different:current_period'],
        ]);

        $currentSections = Section::with('activeTeacher')->where('period', $request->current_period)->get();

        if ($currentSections->isEmpty()) {
            return back()->with('error', __('No sections found for the period :period.', ['period' => $request->current_period]));
        }

        $created = 0;

        DB::transaction(function () use ($currentSections, $request, &$created) {
            foreach ($currentSections as $section) {
                // Evitar duplicados si se pulsa "clonar" dos veces.
                $exists = Section::where('name', $section->name)
                    ->where('trayecto_id', $section->trayecto_id)
                    ->where('period', $request->new_period)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $newSection = Section::create([
                    'name' => $section->name,
                    'trayecto_id' => $section->trayecto_id,
                    'max_team_size' => $section->max_team_size,
                    'period' => $request->new_period,
                    'is_active' => true,
                ]);

                // Copiar el asesor activo para que la nueva sección sea usable.
                $advisor = $section->activeTeacher->first();
                if ($advisor) {
                    $newSection->teachers()->attach($advisor->id, ['is_active' => true]);
                }

                $created++;
            }
        });

        AuditLog::record('clone_period', 'Clonó ' . $created . ' sección(es) al periodo ' . $request->new_period . '.');

        return redirect()->route('sections.index')->with('success', __('Sections cloned to period :period successfully.', ['period' => $request->new_period]));
    }

}