<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Outil;
use App\Models\Publication;
use App\Services\PublicationService;
use Illuminate\Http\RedirectResponse;

class MediaController extends Controller
{
    public function destroy(Media $media, PublicationService $publications): RedirectResponse
    {
        $cible = $media->cible;

        if ($cible instanceof Publication) {
            $this->authorize('update', $cible);
        } elseif ($cible instanceof Outil) {
            $this->authorize('update', $cible);
        } else {
            abort(403);
        }

        $publications->supprimerMedia($media);

        return back()->with('status', __('interface.media_deleted'));
    }
}
