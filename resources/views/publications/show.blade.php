<publication>
    <h1>{{ $publication->titre }}</h1>
    <p>Par {{ $publication->utilisateur->name }}</p>
    <p>{{ $publication->description }}</p>
</publication>

<h2>Description</h2>
@foreach ($publication->description as $description)
    <publication>
        <h3>{{ $description }}</h3>
        <p>TODO: display content</p>
    </publication>
@endforeach
