<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserStatus;

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

        return view('users.students', compact('students'));
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
}