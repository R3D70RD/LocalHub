<h1>Publications</h1>

<label for="search">Rechercher une publication</label>
<input id="search" type="search" autocomplete="off"
       data-search-url="{{ route('recherche.publications') }}"
       data-searching="Recherche en cours..."
       data-empty="Aucun résultat."
       data-count=":count résultat(s)."
       data-error="Recherche temporairement indisponible.">
<p id="search-status" role="status" aria-live="polite"></p>
<ul id="search-results"></ul>

@forelse ($publications as $publication)
    <publication>
        <h2><a href="{{ route('publications.show', $publication) }}">{{ $publication->titre }}</a></h2>
        <p>Par {{ $publication->utilisateur->nom }}</p>
        <p>{{ $publication->description }}</p>
    </publication>
@empty
    <p>Aucune publication.</p>
@endforelse

{{ $publications->links() }}

@push('scripts')
    <script src="{{ asset('js/recherche.js') }}" defer></script>
@endpush

