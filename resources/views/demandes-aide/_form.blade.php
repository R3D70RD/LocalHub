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
            <label for="date_limite" class="{{ $labelClass }}">{{ __('interface.deadline') }}</label>
            <input id="date_limite" name="date_limite" type="date"
                   value="{{ old('date_limite', $entite?->date_limite?->format('Y-m-d')) }}"
                   class="{{ $inputClass }}">
            @error('date_limite') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-end">
            <input type="hidden" name="urgent" value="0">
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="urgent" value="1" @checked(old('urgent', $entite?->urgent))>
                {{ __('interface.urgent') }}
            </label>
            @error('urgent') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
