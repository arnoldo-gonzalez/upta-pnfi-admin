<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\Section;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'credential' => ['required', 'numeric', 'digits_between:6,9', 'unique:'.User::class],
            'phone' => ['required', 'numeric', 'digits_between:10,11'],
            'section_id' => ['required', 'exists:sections,id'], 
        ]);

        $section = Section::find($request->section_id);
        
        if (!$section || !$section->is_active) {
            return back()->withInput()->withErrors([
                'section_id' => __('The selected section is closed or does not exist.')
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'credential' => $request->credential,
            'phone' => $request->phone,
            'status_id' => UserStatus::PENDING->value,
        ]);

        $user->assignRole('estudiante');

        // CREAR LA INSCRIPCIÓN (ENROLLMENT)
        Enrollment::create([
            'user_id' => $user->id,
            'section_id' => $section->id,
            'is_active' => true,
        ]);

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Tu cuenta ha sido creada. Por favor, espera a que tu asesor apruebe tu registro para poder ingresar.');
    }
}
