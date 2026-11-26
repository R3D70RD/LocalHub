<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Outil;
use App\Models\Publication;
use App\Services\PublicationService;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function destroy(Media $media, PublicationService $publications): JsonResponse
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

        return response()->json(null, 204);
    }
}
