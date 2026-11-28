@php
    use App\Models\Category;

    $inputClass = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-sm text-rose-600';

    $categories = Category::query()->where('actif', true)->orderBy('nom')->get();
    $currentCategorie = old('id_categorie', $entite?->id_categorie);
@endphp

<div class="space-y-5">
    <div>
        <label for="titre" class="{{ $labelClass }}">{{ __('interface.title') }}</label>
        <input id="titre" name="titre" value="{{ old('titre', $entite?->titre) }}" class="{{ $inputClass }}">
        @error('titre') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="{{ $labelClass }}">{{ __('interface.content') }}</label>
        <textarea id="description" name="description" rows="6" class="{{ $inputClass }}">{{ old('description', $entite?->description) }}</textarea>
        @error('description') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="id_categorie" class="{{ $labelClass }}">{{ __('interface.category') }}</label>
            <select id="id_categorie" name="id_categorie" class="{{ $inputClass }}">
                <option value="">{{ __('interface.no_category') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id_categorie }}" @selected((int) $currentCategorie === $category->id_categorie)>
                        {{ $category->nom }}
                    </option>
                @endforeach
            </select>
            @error('id_categorie') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="portee" class="{{ $labelClass }}">{{ __('interface.scope_label') }}</label>
            <select id="portee" name="portee" class="{{ $inputClass }}">
                <option value="quartier" @selected(old('portee', $entite?->portee?->value ?? 'quartier') === 'quartier')>{{ __('interface.scope_quartier') }}</option>
                <option value="ville" @selected(old('portee', $entite?->portee?->value ?? 'quartier') === 'ville')>{{ __('interface.scope_ville') }}</option>
                <option value="pays" @selected(old('portee', $entite?->portee?->value ?? 'quartier') === 'pays')>{{ __('interface.scope_pays') }}</option>
            </select>
            @error('portee') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="type_annonce" class="{{ $labelClass }}">{{ __('interface.annonce_type') }}</label>
            <select id="type_annonce" name="type_annonce" class="{{ $inputClass }}">
                <option value="vente" @selected(old('type_annonce', $entite?->type_annonce ?? 'vente') === 'vente')>{{ __('interface.type_vente') }}</option>
                <option value="don" @selected(old('type_annonce', $entite?->type_annonce) === 'don')>{{ __('interface.type_don') }}</option>
                <option value="recherche" @selected(old('type_annonce', $entite?->type_annonce) === 'recherche')>{{ __('interface.type_recherche') }}</option>
            </select>
            @error('type_annonce') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="prix" class="{{ $labelClass }}">{{ __('interface.price') }}</label>
            <input id="prix" name="prix" type="number" step="0.01" min="0"
                   value="{{ old('prix', $entite?->prix) }}" class="{{ $inputClass }}">
            <p class="mt-1 text-xs text-slate-500">{{ __('interface.free_price_hint') }}</p>
            @error('prix') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
