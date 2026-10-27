<article>
    <h1>{{ $publication->titre }}</h1>
    <p>Par {{ $publication->utilisateur->nom }}</p>
    <p>{{ $publication->description }}</p>
    <p><a href="{{ route('publications.edit', $publication) }}">Modifier</a></p>
    <form method="post" action="{{ route('publications.destroy', $publication) }}">
        @csrf
        @method('DELETE')
        <button type="submit">Supprimer</button>
    </form>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif
</article>
