<?php

namespace App\Http\Controllers;

use App\Enums\TutorRequestStatus;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectEvaluation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = User::role('docente')->orderBy('name')->paginate(10);
        return view('teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('teachers.create');
    }

    public function edit(User $teacher)
    {
        $this->ensureIsTeacher($teacher);

        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        $this->ensureIsTeacher($teacher);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($teacher->id)],
            'credential' => ['required', 'numeric', 'digits_between:6,9', Rule::unique('users')->ignore($teacher->id)],
            'phone' => ['required', 'numeric', 'digits_between:10,11'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'availability' => ['nullable', 'string', 'max:255'],
            'jury_available' => ['sometimes', 'boolean'],
            'max_tutored_projects' => ['required', 'integer', 'min:1'],
            'status_id' => ['required', Rule::in([UserStatus::ACTIVE->value, UserStatus::REJECTED->value])],
            'password' => ['nullable', 'string', Password::defaults()],
        ]);

        $teacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'credential' => $request->credential,
            'max_tutored_projects' => $request->max_tutored_projects,
            'phone' => $request->phone,
            'specialty' => $request->specialty,
            'availability' => $request->availability,
            'jury_available' => $request->boolean('jury_available'),
            'status_id' => $request->status_id,
        ]);

        // Si el coordinador escribió una nueva contraseña, la actualizamos
        if ($request->filled('password')) {
            $teacher->update([
                'password' => Hash::make($request->password),
            ]);
        }

        AuditLog::record('update_teacher', 'Editó la cuenta del docente: ' . $teacher->name);

        return redirect()->route('teachers.index')
                         ->with('success', __('Teacher updated successfully.'));
    }

    public function destroy(User $teacher)
    {
        $this->ensureIsTeacher($teacher);

        if ((int) $teacher->id === (int) auth()->id()) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        // No borrar docentes con historial académico: la cascada eliminaría
        // evaluaciones y documentos. Se recomienda desactivar la cuenta.
        $hasHistory = ProjectEvaluation::where('user_id', $teacher->id)->exists()
            || ProjectDocument::where('user_id', $teacher->id)->exists()
            || Project::where('tutor_id', $teacher->id)->exists();

        if ($hasHistory) {
            return back()->with('error', __('This teacher has academic history. Deactivate the account instead of deleting it.'));
        }

        $teacherName = $teacher->name;

        DB::transaction(function () use ($teacher) {
            // No dejar proyectos con estado "aceptado" apuntando a un tutor borrado.
            Project::where('tutor_id', $teacher->id)->update([
                'tutor_id' => null,
                'tutor_request_status_id' => TutorRequestStatus::REJECTED->value,
            ]);

            $teacher->delete();
        });

        AuditLog::record('delete_teacher', 'Eliminó la cuenta del docente: ' . $teacherName);

        return redirect()->route('teachers.index')
                         ->with('success', __('Teacher deleted successfully.'));
    }

    private function ensureIsTeacher(User $teacher): void
    {
        abort_unless($teacher->hasRole('docente'), 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'credential' => ['required', 'numeric', 'digits_between:6,9', 'unique:users,credential'],
            'phone' => ['required', 'numeric', 'digits_between:10,11'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'availability' => ['nullable', 'string', 'max:255'],
            'jury_available' => ['sometimes', 'boolean'],
            'max_tutored_projects' => ['required', 'integer', 'min:1'],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'credential' => $request->credential,
            'phone' => $request->phone,
            'specialty' => $request->specialty,
            'availability' => $request->availability,
            'jury_available' => $request->boolean('jury_available', true),
            'password' => Hash::make($request->password),
            'max_tutored_projects' => $request->max_tutored_projects,
            'status_id' => UserStatus::ACTIVE->value,
        ]);

        // 2. Le asignamos el rol de sistema de Spatie
        $teacher->assignRole('docente');

        AuditLog::record('create_teacher', 'Creó la cuenta del docente: ' . $teacher->name);

        return redirect()->route('teachers.index')
                         ->with('success', __('Teacher created successfully.'));
    }
}