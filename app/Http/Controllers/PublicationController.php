<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use app\Models\Publication;

class PublicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $publication = Publication::query()
            ->with('utilisateur:id,name')
            ->whereNotNull('date_creation')
            ->latest('date_creation')
            ->paginate(10);

        return view('publication.index', compact('publication'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Publication $publication): View
    {
        abort_if($publication->date_creation === null, 404);

        return view('publication.show', [
            'publication' => $publication->load('utilisateur', 'description'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
