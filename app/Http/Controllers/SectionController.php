<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Trayecto;
use App\Models\User;
use Illuminate\Http\Request;

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

        if ($request->filled('teacher_id')) {
            
            $activeTeacherIds = $section->teachers()->wherePivot('is_active', true)->pluck('users.id')->toArray();
            
            if (!empty($activeTeacherIds)) {
                $section->teachers()->updateExistingPivot($activeTeacherIds, ['is_active' => false]);
            }

            $section->teachers()->syncWithoutDetaching([
                $request->teacher_id => ['is_active' => true]
            ]);
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
        ]);

        $section = Section::create([
            'name' => $request->name,
            'trayecto_id' => $request->trayecto_id,
            'max_team_size' => $request->max_team_size,
        ]);

        if ($request->filled('teacher_id')) {
            $section->teachers()->attach($request->teacher_id, [
                'is_active' => true
            ]);
        }

        return redirect()->route('sections.index')->with('success', __('Section created successfully.'));
    }
}