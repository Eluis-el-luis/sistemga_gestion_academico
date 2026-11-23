<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        try {
            return view('profile.edit', [
                'user' => $request->user(),
            ]);
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $this->formatearError($e, 'No fue posible cargar los datos de tu perfil.'));
        }
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $user = $request->user();
            $user->fill($validated);

            if ($request->hasFile('avatar')) {
                if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            }

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return Redirect::route('profile.edit')
                ->with('error', $this->formatearError($e, 'No fue posible actualizar tu perfil.'));
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
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
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return Redirect::route('profile.edit')
                ->with('error', $this->formatearError($e, 'No fue posible eliminar la cuenta.'));
        }
    }
}