<?php

namespace Tests\Feature;

use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreeContexteGeo;
use Tests\TestCase;

class NotificationWebTest extends TestCase
{
    use CreeContexteGeo;
    use RefreshDatabase;

    public function test_les_notifications_sont_listees_et_marquees_lues(): void
    {
        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);

        $notification = Notification::create([
            'type' => 'pret.statut',
            'notifiable_type' => $alex->getMorphClass(),
            'notifiable_id' => $alex->id_utilisateur,
            'data' => ['titre_outil' => 'Perceuse', 'statut' => 'approuve'],
        ]);

        $this->actingAs($alex)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Perceuse');

        $this->actingAs($alex)
            ->post(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->lu_at);

        Notification::create([
            'type' => 'pret.message',
            'notifiable_type' => $alex->getMorphClass(),
            'notifiable_id' => $alex->id_utilisateur,
            'data' => ['apercu' => 'Bonjour'],
        ]);

        $this->actingAs($alex)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $alex->unreadNotifications()->count());
    }

    public function test_un_utilisateur_ne_peut_pas_lire_la_notification_d_un_autre(): void
    {
        $geo = $this->creerContexteGeo();
        $alex = $this->utilisateurGeo('Tremblay', $geo['montreal'], $geo['plateau']);
        $emma = $this->utilisateurGeo('Bouchard', $geo['montreal'], $geo['verdun']);

        $notification = Notification::create([
            'type' => 'pret.statut',
            'notifiable_type' => $alex->getMorphClass(),
            'notifiable_id' => $alex->id_utilisateur,
            'data' => ['titre_outil' => 'Perceuse'],
        ]);

        $this->actingAs($emma)
            ->post(route('notifications.read', $notification))
            ->assertForbidden();
    }
}
