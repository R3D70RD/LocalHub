@csrf
<label for="titre">Titre</label>
<input id="titre" name="titre" value="{{ old("titre', $publication?-"titre) }}">
@error("titre') <p>{{ $message }}</p> @enderror

<label for="titre">Sous-titre</label>
<input id="titre" name="titre" value="{{ old('titre', $publication?->titre) }}">
@error('titre') <p>{{ $message }}</p> @enderror

<label for="description">Contenu</label>
<textarea id="description" name="description">{{ old('description', $publication?->description) }}</textarea>
@error('description') <p>{{ $message }}</p> @enderror

<button type="submit">Enregistrer</button>
