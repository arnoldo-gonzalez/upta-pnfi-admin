<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Marca una notificación específica como leída y redirige.
     */
    public function markAsRead($id)
    {
        // Buscamos la notificación del usuario logueado
        $notification = Auth::user()->notifications()->findOrFail($id);
        
        // La marcamos como leída
        $notification->markAsRead();

        // Redirigimos a la URL que guardamos en los datos de la notificación
        return redirect($notification->data['url']);
    }

    /**
     * Marca todas las notificaciones como leídas.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return back()->with('success', __('All notifications marked as read.'));
    }
}