<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function lire(Notification $notification, Request $request): RedirectResponse
    {
        $this->verifierProprietaire($notification, $request);

        $notification->marquerCommeLue();

        return back();
    }

    public function toutLire(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['lu_at' => now()]);

        return back()->with('status', __('interface.notifications_read'));
    }

    private function verifierProprietaire(Notification $notification, Request $request): void
    {
        abort_unless(
            $notification->notifiable_type === $request->user()->getMorphClass()
            && (int) $notification->notifiable_id === (int) $request->user()->id_utilisateur,
            403,
        );
    }
}
