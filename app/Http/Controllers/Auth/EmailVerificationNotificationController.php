<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        try {
            if ($request->user()?->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard', absolute: false));
            }

            $request->user()?->sendEmailVerificationNotification();

            return back()->with('status', 'verification-link-sent');
        } catch (\Throwable $e) {
            Log::error('Error al enviar notificación de verificación de correo', [
                'user_id' => $request->user()?->id,
                'exception' => $e->getMessage(),
            ]);

            return back()->with('error', 'No fue posible enviar el correo de verificación.');
        }
    }
}