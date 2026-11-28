<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnregistrerOutilRequest;
use App\Models\Outil;
use App\Services\OutilService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutilController extends Controller
{
    public function __construct(private readonly OutilService $outils) {}

    public function index(Request $request): View
    {
        $outils = $this->outils->liste($request->user(), [
            'scope' => $request->query('scope'),
            'q' => $request->query('q'),
            'par_page' => 12,
        ]);

        return view('outils.index', [
            'outils' => $outils,
            'scope' => in_array($request->query('scope'), ['quartier', 'ville', 'pays'], true)
                ? $request->query('scope')
                : 'quartier',
        ]);
    }

    public function create(): View
    {
        return view('outils.create');
    }

    public function store(EnregistrerOutilRequest $request): RedirectResponse
    {
        $outil = $this->outils->creer(
            $request->user(),
            $request->safe()->except('images'),
            $request->file('images', []),
        );

        return redirect()->route('outils.show', $outil)
            ->with('status', __('interface.tool_created'));
    }

    public function show(Outil $outil): View
    {
        $outil->load(['proprietaire', 'categorie', 'medias']);

        return view('outils.show', [
            'outil' => $outil,
            'pretActif' => $outil->pretActif(),
        ]);
    }

    public function edit(Outil $outil): View
    {
        $this->authorize('update', $outil);

        return view('outils.edit', compact('outil'));
    }

    public function update(EnregistrerOutilRequest $request, Outil $outil): RedirectResponse
    {
        $this->authorize('update', $outil);

        $this->outils->mettreAJour(
            $outil,
            $request->safe()->except('images'),
            $request->file('images', []),
        );

        return redirect()->route('outils.show', $outil)
            ->with('status', __('interface.tool_updated'));
    }

    public function destroy(Outil $outil): RedirectResponse
    {
        $this->authorize('delete', $outil);

        $this->outils->supprimer($outil);

        return redirect()->route('outils.index')
            ->with('status', __('interface.tool_deleted'));
    }
}
