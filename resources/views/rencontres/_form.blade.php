@php
    use App\Models\Category;

    $inputClass = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-sm text-rose-600';

    $categories = Category::query()->where('actif', true)->orderBy('nom')->get();
    $currentCategorie = old('id_categorie', $entite?->id_categorie);
    $interets = old('interets', $entite?->interets ? implode(', ', $entite->interets) : '');
    $interets = is_array($interets) ? implode(', ', $interets) : $interets;
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
            <label for="date_heure" class="{{ $labelClass }}">{{ __('interface.meeting_time') }}</label>
            <input id="date_heure" name="date_heure" type="datetime-local"
                   value="{{ old('date_heure', $entite?->date_heure?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass }}">
            @error('date_heure') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="lieu" class="{{ $labelClass }}">{{ __('interface.location') }}</label>
            <input id="lieu" name="lieu" value="{{ old('lieu', $entite?->lieu) }}" class="{{ $inputClass }}">
            @error('lieu') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="capacite" class="{{ $labelClass }}">{{ __('interface.capacity') }}</label>
            <input id="capacite" name="capacite" type="number" min="1"
                   value="{{ old('capacite', $entite?->capacite) }}" class="{{ $inputClass }}">
            @error('capacite') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="interets" class="{{ $labelClass }}">{{ __('interface.interests') }}</label>
            <input id="interets" name="interets" value="{{ $interets }}" class="{{ $inputClass }}"
                   placeholder="{{ __('interface.interests_placeholder') }}">
            <p class="mt-1 text-xs text-slate-500">{{ __('interface.interests_hint') }}</p>
            @error('interets') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            @error('interets.*') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
