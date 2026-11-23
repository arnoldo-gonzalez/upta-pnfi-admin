<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SecurityQuestionPasswordController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.security-question-request');
    }

    public function findQuestion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! $user->security_question || ! $user->security_answer) {
            return back()->withInput()->withErrors([
                'email' => __('No hay una pregunta de seguridad configurada para ese correo.'),
            ]);
        }

        $request->session()->put('security_reset_email', $user->email);

        return redirect()->route('password.security.question');
    }

    public function questionForm(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('security_reset_email');

        if (! $email) {
            return redirect()->route('password.security.request');
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! $user->security_question) {
            $request->session()->forget('security_reset_email');

            return redirect()->route('password.security.request');
        }

        return view('auth.security-question-reset', [
            'email' => $user->email,
            'question' => $user->security_question,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'security_answer' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $key = 'security-reset|' . Str::lower($validated['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'security_answer' => __('Demasiados intentos. Intenta de nuevo en :seconds segundos.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! $user->security_answer || ! Hash::check($validated['security_answer'], $user->security_answer)) {
            RateLimiter::hit($key);

            return back()->withInput()->withErrors([
                'security_answer' => __('La respuesta de seguridad no es correcta.'),
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        RateLimiter::clear($key);
        $request->session()->forget('security_reset_email');

        AuditLog::record('reset_password_security_question', 'Restableció su contraseña con pregunta de seguridad.');

        return redirect()->route('login')->with('status', __('Tu contraseña fue restablecida. Ya puedes iniciar sesión.'));
    }
}
