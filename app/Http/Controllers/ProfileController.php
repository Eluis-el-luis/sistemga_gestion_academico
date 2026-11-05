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
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si falla al cargar la vista de perfil
            return redirect()->route('dashboard')->with('error', 'Ocurrió un problema al cargar los datos de tu perfil.');
        }
    }


    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Llena el modelo con los datos validados (nombre_completo)
        $user->fill($request->validated());

        // --- LÓGICA PARA GUARDAR EL AVATAR ---
        if ($request->hasFile('avatar')) {
            // Si el usuario ya tenía una foto, la borramos del disco para no acumular basura
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            
            // Guardamos la nueva foto en la carpeta "avatars" dentro del disco público
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }
        // -------------------------------------

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                throw $e;
            }
            // CONTINGENCIA: Si ocurre un fallo crítico al eliminar la cuenta en cascada
            return Redirect::route('profile.edit')->with('error', 'No se pudo eliminar la cuenta debido a un error del servidor. Por favor, contacta a soporte.');
        }
    }
}