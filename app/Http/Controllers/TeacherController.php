<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
        return view('teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($teacher->id)],
            'credential' => ['required', 'numeric', 'digits_between:6,9', Rule::unique('users')->ignore($teacher->id)],
            'phone' => ['required', 'numeric', 'digits_between:10,11'],
            'max_tutored_projects' => ['required', 'integer', 'min:1'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $teacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'credential' => $request->credential,
            'max_tutored_projects' => $request->max_tutored_projects,
            'phone' => $request->phone,
        ]);

        // Si el coordinador escribió una nueva contraseña, la actualizamos
        if ($request->filled('password')) {
            $teacher->update([
                'password' => Hash::make($request->password)
            ]);
        }

        return redirect()->route('teachers.index')
                         ->with('success', __('Teacher updated successfully.'));
    }

    public function destroy(User $teacher)
    {
        $teacher->delete();

        return redirect()->route('teachers.index')
                         ->with('success', __('Teacher deleted successfully.'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'credential' => ['required', 'numeric', 'digits_between:6,9', 'unique:users,credential'],
            'phone' => ['required', 'numeric', 'digits_between:10,11'],
            'max_tutored_projects' => ['required', 'integer', 'min:1'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'credential' => $request->credential,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'max_tutored_projects' => $request->max_tutored_projects,
            'status_id' => UserStatus::ACTIVE->value,
        ]);

        // 2. Le asignamos el rol de sistema de Spatie
        $teacher->assignRole('docente');

        return redirect()->route('teachers.index')
                         ->with('success', __('Teacher created successfully.'));
    }
}