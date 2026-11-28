<?php

namespace App\Http\Controllers;

use App\Services\FeedService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FilController extends Controller
{
    public function __construct(private readonly FeedService $feed) {}

    public function index(Request $request): View
    {
        $estVisiteur = $request->user() === null;
        $scope = $estVisiteur ? 'pays' : $this->feed->normaliserScope($request->query('scope'));

        $fil = $this->feed->enrichir($this->feed->filUnifie($request->user(), [
            'scope' => $scope,
            'q' => $request->query('q'),
            'par_page' => 10,
        ]));

        return view('fil.index', compact('fil', 'scope', 'estVisiteur'));
    }
}
