<?php

namespace App\Http\Controllers;

use App\Models\ProjectStudent;
use App\Models\User;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Notifications\AccountStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentController extends Controller
{
    /**
     * Muestra el listado de estudiantes (filtrado por rol).
     */
    public function index()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        
        $query = User::role('estudiante')
            ->with([
                'status',
                'enrollments' => function($query) {
                    $query->where('is_active', true)->with('section.trayecto');
                }
            ]);

        // Si es docente, solo ve a los estudiantes de sus secciones activas
        if ($currentUser->hasRole('docente')) {
            $query->whereHas('enrollments', function($q) use ($currentUser) {
                $q->where('is_active', true)
                  ->whereHas('section.activeTeacher', function($sub) use ($currentUser) {
                      $sub->where('users.id', $currentUser->id);
                  });
            });
        }

        $students = $query->orderBy('name', 'asc')->paginate(15);

        return view('students.index', compact('students'));
    }

    // --- ACCIONES INDIVIDUALES ---

    public function approve(User $student)
    {
        $this->authorizeStudent($student, allowAdvisor: true);

        if ($student->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('Only pending students can be approved.'));
        }

        $student->update(['status_id' => UserStatus::ACTIVE->value]);

        AuditLog::record('approve_student', 'Aprobó la cuenta del estudiante: ' . $student->name);

        $student->notify(new AccountStatusChanged(
            'Cuenta aprobada',
            'Tu cuenta fue aprobada. Ya puedes iniciar sesión en el sistema.'
        ));

        return back()->with('success', __('The student :name has been successfully approved.', ['name' => $student->name]));
    }

    public function reject(User $student)
    {
        $this->authorizeStudent($student, allowAdvisor: true);

        if ($student->status_id !== UserStatus::PENDING->value) {
            return back()->with('error', __('Only pending students can be rejected.'));
        }

        $student->update(['status_id' => UserStatus::REJECTED->value]);

        AuditLog::record('reject_student', 'Rechazó la cuenta del estudiante: ' . $student->name);

        $student->notify(new AccountStatusChanged(
            'Cuenta rechazada',
            'Tu solicitud de registro fue rechazada. Contacta a la coordinación para más información.'
        ));

        return back()->with('success', __('The student :name has been successfully rejected.', ['name' => $student->name]));
    }

    public function edit(User $student)
    {
        $this->authorizeStudent($student);

        return view('students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        $this->authorizeStudent($student);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student->id)],
            'credential' => ['required', 'numeric', 'digits_between:6,9', Rule::unique('users')->ignore($student->id)],
            'phone' => ['required', 'numeric', 'digits_between:10,11'],
            'status_id' => [
                'required',
                Rule::in([
                    UserStatus::PENDING->value,
                    UserStatus::ACTIVE->value,
                    UserStatus::REJECTED->value,
                ]),
            ],
            'password' => ['nullable', 'string', Password::defaults()],
        ]);

        $student->update([
            'name' => $request->name,
            'email' => $request->email,
            'credential' => $request->credential,
            'phone' => $request->phone,
            'status_id' => $request->status_id,
        ]);

        if ($request->filled('password')) {
            $student->update(['password' => Hash::make($request->password)]);
        }

        AuditLog::record('update_student', 'Editó la cuenta del estudiante: ' . $student->name);

        return redirect()->route('students.index')->with('success', __('Student updated successfully.'));
    }

    public function destroy(User $student)
    {
        $this->authorizeStudent($student);

        if (ProjectStudent::whereHas('enrollment', function ($query) use ($student) {
            $query->where('user_id', $student->id);
        })->exists()) {
            return back()->with('error', __('This student belongs to a project and cannot be deleted. Deactivate the account instead.'));
        }

        $student->delete();

        AuditLog::record('delete_student', 'Eliminó la cuenta del estudiante: ' . $student->name);

        return redirect()->route('students.index')->with('success', __('Student deleted successfully.'));
    }

    /**
     * Método de seguridad reutilizable.
     * Verifica si el usuario actual tiene permiso para gestionar a este estudiante.
     */
    private function authorizeStudent(User $student, bool $allowAdvisor = false)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        // El route-model-binding acepta cualquier User: el objetivo debe ser estudiante.
        abort_unless($student->hasRole('estudiante'), 404);

        // Admin y coordinador tienen vía libre sobre estudiantes.
        if ($currentUser->hasAnyRole(['admin', 'coordinador'])) {
            return;
        }

        // El asesor puede aprobar/rechazar, pero no editar datos ni borrar cuentas.
        if ($allowAdvisor && $currentUser->hasRole('docente')) {
            $hasAccess = $student->enrollments()
                ->where('is_active', true)
                ->whereHas('section.activeTeacher', function ($q) use ($currentUser) {
                    $q->where('users.id', $currentUser->id);
                })->exists();

            if ($hasAccess) {
                return;
            }
        }

        abort(403, 'No tienes permiso para gestionar a este estudiante.');
    }
}