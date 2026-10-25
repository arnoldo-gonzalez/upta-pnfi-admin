<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
            'credential' => ['required', 'numeric', 'digits_between:6,9', 'unique:users,credential'],
            'phone' => ['required', 'numeric', 'digits_between:10,11'], // Sin unique
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,

            'credential' => $request->credential,
            'phone' => $request->phone,

            'password' => Hash::make($request->password),

            'status_id' => UserStatus::PENDING->value
        ]);

        $user->assignRole("estudiante");

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Tu cuenta ha sido creada. Por favor, espera a que tu asesor académico apruebe tu registro para poder ingresar.');
    }
}
