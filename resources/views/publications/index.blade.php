<h1>Publications</h1>

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
