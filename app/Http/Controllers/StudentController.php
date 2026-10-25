<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index()
    {
        $students = User::role('estudiante')
            ->with('status')
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('students.index', compact('students'));
    }

    /**
     * Approve a pending student.
     */
    public function approve(User $student)
    {
        if (!$student->hasRole('estudiante')) {
            return back()->with('error', __('The selected user is not a student.'));
        }

        $student->update([
            'status_id' => UserStatus::ACTIVE->value
        ]);

        return back()->with('success', __('The student :name has been successfully approved.', ['name' => $student->name]));
    }

    /**
     * Reject a pending student.
     */
    public function reject(User $student)
    {
        if (!$student->hasRole('estudiante')) {
            return back()->with('error', __('The selected user is not a student.'));
        }

        $student->update([
            'status_id' => UserStatus::REJECTED->value
        ]);

        return back()->with('success', __('The student :name has been successfully rejected.', ['name' => $student->name]));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(User $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, User $student)
    {
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
            $student->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return redirect()->route('students.index')
                         ->with('success', __('Student updated successfully.'));
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(User $student)
    {
        $student->delete();

        return redirect()->route('students.index')
                         ->with('success', __('Student deleted successfully.'));
    }
}