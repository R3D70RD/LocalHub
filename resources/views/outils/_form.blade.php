@php
    use App\Models\Category;

    $inputClass = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500';
    $labelClass = 'mb-1 block text-sm font-medium text-slate-700';
    $errorClass = 'mt-1 text-sm text-rose-600';

    $categories = Category::query()->where('actif', true)->orderBy('nom')->get();
    $currentCategorie = old('id_categorie', $outil?->id_categorie);
@endphp

<div class="space-y-5">
    <div>
        <label for="titre" class="{{ $labelClass }}">{{ __('interface.title') }}</label>
        <input id="titre" name="titre" value="{{ old('titre', $outil?->titre) }}" class="{{ $inputClass }}">
        @error('titre') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="{{ $labelClass }}">{{ __('interface.content') }}</label>
        <textarea id="description" name="description" rows="5" class="{{ $inputClass }}">{{ old('description', $outil?->description) }}</textarea>
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
                <option value="quartier" @selected(old('portee', $outil?->portee?->value ?? 'quartier') === 'quartier')>{{ __('interface.scope_quartier') }}</option>
                <option value="ville" @selected(old('portee', $outil?->portee?->value ?? 'quartier') === 'ville')>{{ __('interface.scope_ville') }}</option>
                <option value="pays" @selected(old('portee', $outil?->portee?->value ?? 'quartier') === 'pays')>{{ __('interface.scope_pays') }}</option>
            </select>
            @error('portee') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="etat" class="{{ $labelClass }}">{{ __('interface.tool_condition') }}</label>
            <select id="etat" name="etat" class="{{ $inputClass }}">
                <option value="neuf" @selected(old('etat', $outil?->etat ?? 'bon') === 'neuf')>{{ __('interface.condition_neuf') }}</option>
                <option value="bon" @selected(old('etat', $outil?->etat ?? 'bon') === 'bon')>{{ __('interface.condition_bon') }}</option>
                <option value="usage" @selected(old('etat', $outil?->etat ?? 'bon') === 'usage')>{{ __('interface.condition_usage') }}</option>
            </select>
            @error('etat') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="caution" class="{{ $labelClass }}">{{ __('interface.tool_deposit') }}</label>
            <input id="caution" name="caution" type="number" step="0.01" min="0"
                   value="{{ old('caution', $outil?->caution) }}" class="{{ $inputClass }}">
            @error('caution') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="images" class="{{ $labelClass }}">{{ __('interface.images') }}</label>
            <input id="images" name="images[]" type="file" multiple
                   accept="image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-700">
            @error('images') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            @error('images.*') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            {{ __('interface.save') }}
        </button>
        <a href="{{ route('outils.index') }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">
            {{ __('interface.cancel') }}
        </a>
    </div>
</div>
