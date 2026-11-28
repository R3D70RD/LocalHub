<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FilResource;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class FilController extends Controller
{
    public function __construct(private readonly FeedService $feed) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $fil = $this->feed->enrichir($this->feed->filUnifie(Auth::guard('sanctum')->user(), [
            'scope' => $request->query('scope'),
            'q' => $request->query('q'),
            'par_page' => (int) $request->query('par_page', 15),
        ]));

        return FilResource::collection($fil);
    }
}
