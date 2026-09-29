<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
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
        try {
            return view('profile.edit', [
                'user' => $request->user(),
            ]);
        } catch (\Exception $e) {
            // CONTINGENCIA: Si falla al cargar la vista de perfil
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al cargar los datos de tu perfil.');
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // La validación del ProfileUpdateRequest ya se ejecuta de forma segura antes de entrar aquí
        try {
            $user = $request->user();
            $user->fill($request->validated());

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return Redirect::route('profile.edit')->with('status', 'profile-updated');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si la base de datos rechaza la actualización del perfil
            return Redirect::route('profile.edit')->with('error', 'Hubo un error técnico al intentar actualizar tu información. Inténtalo de nuevo.');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // La validación de la contraseña actual se mantiene afuera para que los errores lleguen al formulario
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        try {
            $user = $request->user();

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/');

        } catch (\Exception $e) {
            // CONTINGENCIA: Si ocurre un fallo crítico al eliminar la cuenta en cascada
            return Redirect::route('profile.edit')->with('error', 'No se pudo eliminar la cuenta debido a un error del servidor. Por favor, contacta a soporte.');
        }
    }
}