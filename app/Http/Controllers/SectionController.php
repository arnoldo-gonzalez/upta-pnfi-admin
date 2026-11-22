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
        $teachers = User::role('docente')->orderBy('name')->get(); 
        
        return view('sections.create', compact('trayectos', 'teachers'));
    }

    public function edit(Section $section)
    {
        $trayectos = Trayecto::orderBy('number')->get();
        $teachers = User::role('docente')->orderBy('name')->get(); 
        
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
            'teacher_id' => ['nullable', 'exists:users,id'], 
        ]);

        $section->update([
            'name' => $request->name,
            'trayecto_id' => $request->trayecto_id,
            'max_team_size' => $request->max_team_size,
        ]);

        // Si se seleccionó un docente, verificamos si es diferente al actual
        if ($request->filled('teacher_id')) {
            
            // Buscamos quién es el docente activo actualmente
            $currentActiveTeacher = $section->teachers()->wherePivot('is_active', true)->first();

            // Solo hacemos el cambio si el docente seleccionado es distinto al actual
            if (!$currentActiveTeacher || $currentActiveTeacher->id !== $request->teacher_id) {
                
                // 1. Desactivamos a TODOS los docentes que estén activos en esta sección
                $section->teachers()->updateExistingPivot(
                    $section->teachers()->wherePivot('is_active', true)->pluck('users.id')->toArray(),
                    ['is_active' => false]
                );

                // 2. Usamos attach() para crear un NUEVO registro histórico (nuevo ID)
                $section->teachers()->attach($request->teacher_id, [
                    'is_active' => true
                ]);
                
            }
            
        }

        return redirect()->route('sections.index')->with('success', __('Section updated successfully.'));
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Section $section)
    {
        $section->delete();

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

    // Cerrar todas las secciones activas (sin importar el periodo, cerrará las que estén abiertas)
    public function closePeriod(Request $request)
    {
        Section::where('is_active', true)->update(['is_active' => false]);

        return redirect()->route('sections.index')->with('success', __('All active sections have been closed.'));
    }

    // Clonar secciones de un periodo a otro
    public function clonePeriod(Request $request)
    {
        $request->validate([
            'current_period' => ['required', 'string'],
            'new_period' => ['required', 'string', 'different:current_period'],
        ]);

        $currentSections = Section::where('period', $request->current_period)->get();

        if ($currentSections->isEmpty()) {
            return back()->with('error', __('No sections found for the period :period.', ['period' => $request->current_period]));
        }

        DB::transaction(function () use ($currentSections, $request) {
            foreach ($currentSections as $section) {
                Section::create([
                    'name' => $section->name,
                    'trayecto_id' => $section->trayecto_id,
                    'max_team_size' => $section->max_team_size,
                    'period' => $request->new_period,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('sections.index')->with('success', __('Sections cloned to period :period successfully.', ['period' => $request->new_period]));
    }

}