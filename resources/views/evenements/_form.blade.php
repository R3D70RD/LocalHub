@php
    use App\Models\Category;

    $inputClass = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-sm text-rose-600';

    $categories = Category::query()->where('actif', true)->orderBy('nom')->get();
    $currentCategorie = old('id_categorie', $entite?->id_categorie);
    $timezone = old('timezone_id', $entite?->timezone_id ?? 'America/Toronto');
    $zones = [
        'America/Toronto', 'America/Vancouver', 'America/Edmonton', 'America/Winnipeg',
        'America/Halifax', 'America/St_Johns', 'America/Regina', 'America/Whitehorse',
        'America/Yellowknife', 'America/Iqaluit', 'UTC',
    ];
    $formatDate = fn ($valeur) => $valeur?->copy()->setTimezone($timezone)->format('Y-m-d\TH:i');
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
            <label for="date_debut" class="{{ $labelClass }}">{{ __('interface.event_start') }}</label>
            <input id="date_debut" name="date_debut" type="datetime-local"
                   value="{{ old('date_debut', $formatDate($entite?->date_debut)) }}" class="{{ $inputClass }}">
            @error('date_debut') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="date_fin" class="{{ $labelClass }}">{{ __('interface.event_end') }}</label>
            <input id="date_fin" name="date_fin" type="datetime-local"
                   value="{{ old('date_fin', $formatDate($entite?->date_fin)) }}" class="{{ $inputClass }}">
            @error('date_fin') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="timezone_id" class="{{ $labelClass }}">{{ __('interface.timezone') }}</label>
            <select id="timezone_id" name="timezone_id" class="{{ $inputClass }}">
                @foreach ($zones as $zone)
                    <option value="{{ $zone }}" @selected($timezone === $zone)>{{ $zone }}</option>
                @endforeach
            </select>
            @error('timezone_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
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
    </div>
</div>
