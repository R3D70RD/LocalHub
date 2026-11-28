@php
    $traductionDisponible = auth()->check()
        && app(\App\Services\TraductionService::class)->estDisponible();
@endphp

@if ($traductionDisponible)
    <div class="mt-3"
         data-traduction
         data-cle="{{ $publication->id_publication }}-{{ md5((string) $publication->description) }}"
         data-cible="{{ app()->getLocale() }}"
         data-url="{{ route('publications.traduire', $publication) }}"
         data-chargement="{{ __('interface.traduction_chargement') }}"
         data-erreur="{{ __('interface.traduction_erreur') }}"
         data-restaurer="{{ __('interface.traduction_restaurer') }}">
        <button type="button"
                data-traduction-bouton
                class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">
            {{ __('interface.traduire') }}
        </button>
        <p data-traduction-texte hidden
           class="mt-2 whitespace-pre-line rounded-md border border-slate-100 bg-slate-50 p-3 text-sm text-slate-700"></p>
    </div>
@endif
