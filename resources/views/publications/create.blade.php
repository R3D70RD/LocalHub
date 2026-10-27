<h1>Nouvelle publication</h1>

<form method="post" action="{{ route('publications.store') }}">
    @csrf

    <p>
        <label for="titre">Titre</label>
        <input id="titre" name="titre" value="{{ old('titre') }}" required>
        @error('titre') <span>{{ $message }}</span> @enderror
    </p>

    <p>
        <label for="description">Description</label>
        <textarea id="description" name="description" required>{{ old('description') }}</textarea>
        @error('description') <span>{{ $message }}</span> @enderror
    </p>

    <p>
        <label for="type">Type</label>
        <input id="type" name="type" value="{{ old('type') }}" required>
        @error('type') <span>{{ $message }}</span> @enderror
    </p>

    <p>
        <label for="prix">Prix</label>
        <input id="prix" name="prix" type="number" step="0.01" value="{{ old('prix') }}">
        @error('prix') <span>{{ $message }}</span> @enderror
    </p>

    <p>
        <label for="ville">Ville</label>
        <input id="ville" name="ville" value="{{ old('ville') }}">
        @error('ville') <span>{{ $message }}</span> @enderror
    </p>

    <p>
        <label for="id_categorie">Catégorie</label>
        <input id="id_categorie" name="id_categorie" type="number" value="{{ old('id_categorie') }}" required>
        @error('id_categorie') <span>{{ $message }}</span> @enderror
    </p>

    <p>
        <label for="statut">Statut</label>
        <input id="statut" name="statut" value="{{ old('statut', 'active') }}" required>
        @error('statut') <span>{{ $message }}</span> @enderror
    </p>

    <button type="submit">Créer</button>
</form>
