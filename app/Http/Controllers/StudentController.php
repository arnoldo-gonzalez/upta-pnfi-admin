<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        $this->authorizeStudent($student);

        AuditLog::record('approve_student', 'Aprobó la cuenta del estudiante: ' . $student->name);
        $student->update(['status_id' => UserStatus::ACTIVE->value]);
        
        return back()->with('success', __('The student :name has been successfully approved.', ['name' => $student->name]));
    }

    public function reject(User $student)
    {
        $this->authorizeStudent($student);

        $student->update(['status_id' => UserStatus::REJECTED->value]);
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
            'status_id' => ['required', 'exists:statuses,id'], 
            'password' => ['nullable', 'string', 'min:8'],
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

        return redirect()->route('students.index')->with('success', __('Student updated successfully.'));
    }

    public function destroy(User $student)
    {
        $this->authorizeStudent($student);

        $student->delete();
        return redirect()->route('students.index')->with('success', __('Student deleted successfully.'));
    }

    /**
     * Método de seguridad reutilizable.
     * Verifica si el usuario actual tiene permiso para gestionar a este estudiante.
     */
    private function authorizeStudent(User $student)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        // Admin y coordinador tienen vía libre
        if ($currentUser->hasAnyRole(['admin', 'coordinador'])) {
            return;
        }

        // Docente: validar que el estudiante esté en una de sus secciones
        if ($currentUser->hasRole('docente')) {
            $hasAccess = $student->enrollments()
                ->where('is_active', true)
                ->whereHas('section.activeTeacher', function($q) use ($currentUser) {
                    $q->where('users.id', $currentUser->id);
                })->exists();

            if ($hasAccess) {
                return;
            }
        }

        // Si no cumple ninguna, bloqueamos
        abort(403, 'No tienes permiso para gestionar a este estudiante.');
    }
}