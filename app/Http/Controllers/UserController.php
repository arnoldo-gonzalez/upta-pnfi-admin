<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'status'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request);
        $role = $validated['role'];

        if ($role === 'estudiante' && empty($validated['section_id'])) {
            return back()->withInput()->withErrors(['section_id' => __('Debes seleccionar una sección para el estudiante.')]);
        }

        DB::transaction(function () use ($validated, $role, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'credential' => $validated['credential'],
                'phone' => $validated['phone'],
                'specialty' => $role === 'docente' ? ($validated['specialty'] ?? null) : null,
                'availability' => $role === 'docente' ? ($validated['availability'] ?? null) : null,
                'jury_available' => $role === 'docente' ? $request->boolean('jury_available', true) : false,
                'password' => Hash::make($validated['password']),
                'status_id' => $validated['status_id'],
                'max_tutored_projects' => $role === 'docente' ? ($validated['max_tutored_projects'] ?? 5) : 0,
            ]);

            $user->assignRole($role);

            if ($role === 'estudiante') {
                Enrollment::create([
                    'user_id' => $user->id,
                    'section_id' => $validated['section_id'],
                    'is_active' => true,
                ]);
            }

            AuditLog::record('create_user', 'Creó el usuario ' . $user->email . ' con rol ' . $role . '.');
        });

        return redirect()->route('admin.users.index')->with('success', __('Usuario creado correctamente.'));
    }

    public function edit(User $user)
    {
        $user->load('roles', 'enrollments.section');

        return view('admin.users.edit', array_merge($this->formData(), compact('user')));
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validateUser($request, $user);
        $role = $validated['role'];

        if ((int) $user->id === (int) auth()->id()) {
            if ($role !== 'admin' || (int) $validated['status_id'] !== UserStatus::ACTIVE->value) {
                return back()->withInput()->withErrors([
                    'role' => __('No puedes quitarte tu propio rol de administrador ni desactivar tu cuenta.'),
                ]);
            }
        }

        $hasEnrollment = $user->enrollments()->where('is_active', true)->exists();

        if ($role === 'estudiante' && ! $hasEnrollment && empty($validated['section_id'])) {
            return back()->withInput()->withErrors(['section_id' => __('Debes seleccionar una sección para el estudiante.')]);
        }

        DB::transaction(function () use ($validated, $role, $user, $hasEnrollment, $request) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'credential' => $validated['credential'],
                'phone' => $validated['phone'],
                'specialty' => $role === 'docente' ? ($validated['specialty'] ?? null) : null,
                'availability' => $role === 'docente' ? ($validated['availability'] ?? null) : null,
                'jury_available' => $role === 'docente' ? $request->boolean('jury_available', true) : false,
                'status_id' => $validated['status_id'],
                'max_tutored_projects' => $role === 'docente'
                    ? ($validated['max_tutored_projects'] ?? $user->max_tutored_projects ?? 5)
                    : $user->max_tutored_projects,
            ]);

            if (! empty($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            $user->syncRoles([$role]);

            if ($role === 'estudiante' && ! $hasEnrollment) {
                Enrollment::create([
                    'user_id' => $user->id,
                    'section_id' => $validated['section_id'],
                    'is_active' => true,
                ]);
            }

            AuditLog::record('update_user', 'Editó el usuario ' . $user->email . ' (rol ' . $role . ').');
        });

        return redirect()->route('admin.users.index')->with('success', __('Usuario actualizado correctamente.'));
    }

    private function formData(): array
    {
        return [
            'roles' => Role::orderBy('name')->pluck('name'),
            'sections' => Section::with('trayecto')->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'credential' => [
                'required',
                'numeric',
                'digits_between:6,9',
                Rule::unique('users', 'credential')->ignore($user?->id),
            ],
            'phone' => ['required', 'numeric', 'digits_between:10,11'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'availability' => ['nullable', 'string', 'max:255'],
            'jury_available' => ['sometimes', 'boolean'],
            'role' => ['required', Rule::in(['admin', 'coordinador', 'docente', 'estudiante'])],
            'status_id' => ['required', Rule::in([
                UserStatus::PENDING->value,
                UserStatus::ACTIVE->value,
                UserStatus::REJECTED->value,
            ])],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::defaults()],
            'section_id' => ['nullable', 'exists:sections,id'],
            'max_tutored_projects' => ['nullable', 'integer', 'min:1'],
        ]);
    }
}
