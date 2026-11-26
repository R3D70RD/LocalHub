<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return NotificationResource::collection(
            $request->user()->notifications()->latest()->paginate(20)
        );
    }

    public function lire(Notification $notification, Request $request): NotificationResource
    {
        $this->verifierProprietaire($notification, $request);
        $notification->marquerCommeLue();

        return new NotificationResource($notification);
    }

    public function toutLire(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['lu_at' => now()]);

        return response()->json(['statut' => 'ok']);
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
