<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $emailChanged = $user->email !== $request->validated('email');

        // Cambiar el correo permite iniciar un reset de contraseña: exigir la clave actual.
        if ($emailChanged) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ]);
        }

        $validated = $request->validated();
        $question = $validated['security_question'] ?? null;
        $answer = $validated['security_answer'] ?? null;

        if ($question && ! $answer && ! $user->security_answer) {
            return back()->withInput()->withErrors([
                'security_answer' => __('Debes registrar una respuesta para la pregunta de seguridad.'),
            ]);
        }

        if (! $question && $answer) {
            return back()->withInput()->withErrors([
                'security_question' => __('Debes registrar la pregunta de seguridad.'),
            ]);
        }

        unset($validated['security_question'], $validated['security_answer']);

        $user->fill($validated);

        if ($question) {
            $user->security_question = $question;
        }

        if ($answer) {
            $user->security_answer = $answer;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            AuditLog::record('change_email', 'Cambió su correo electrónico.');
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
