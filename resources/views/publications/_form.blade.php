<label for="titre">Titre</label>
<input id="titre" name="titre" value="{{ old('titre', $publication?->titre) }}">
@error('titre') <p>{{ $message }}</p> @enderror

<label for="description">Contenu</label>
<textarea id="description" name="description">{{ old('description', $publication?->description) }}</textarea>
@error('description') <p>{{ $message }}</p> @enderror

<label for="type">Type</label>
<input id="type" name="type" value="{{ old('type', $publication?->type) }}">
@error('type') <p>{{ $message }}</p> @enderror

<label for="prix">Prix</label>
<input id="prix" name="prix" type="number" step="0.01" value="{{ old('prix', $publication?->prix) }}">
@error('prix') <p>{{ $message }}</p> @enderror

<label for="ville">Ville</label>
<input id="ville" name="ville" value="{{ old('ville', $publication?->ville) }}">
@error('ville') <p>{{ $message }}</p> @enderror

<label for="id_categorie">Catégorie</label>
<input id="id_categorie" name="id_categorie" type="number" value="{{ old('id_categorie', $publication?->id_categorie) }}">
@error('id_categorie') <p>{{ $message }}</p> @enderror

<label for="statut">Statut</label>
<input id="statut" name="statut" value="{{ old('statut', $publication?->statut ?? 'active') }}">
@error('statut') <p>{{ $message }}</p> @enderror

<label for="image">Image</label>
<input id="image" name="image" type="file"
       accept="image/jpeg,image/png,image/webp">
@error('image') <p>{{ $message }}</p> @enderror

<button type="submit">Enregistrer</button>

