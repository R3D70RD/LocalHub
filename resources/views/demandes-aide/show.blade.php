@extends('layouts.app')

@section('title', $demande->titre)

@section('content')
    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $demande->titre }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('interface.by_author', ['name' => trim(($demande->utilisateur?->prenom ?? '').' '.($demande->utilisateur?->nom ?? ''))]) }}
                    · {{ $demande->created_at?->diffForHumans() }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ __('interface.scope_'.$demande->portee->value) }}</span>
                    @if ($demande->categorie)
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">{{ $demande->categorie->nom }}</span>
                    @endif
                    @if ($demande->urgent)
                        <span class="rounded-full bg-rose-50 px-2.5 py-1 font-medium text-rose-700">{{ __('interface.urgent') }}</span>
                    @endif
                    @if ($demande->date_limite)
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700">{{ __('interface.deadline') }} : {{ $demande->date_limite->toDateString() }}</span>
                    @endif
                </div>
            </div>

            @canany(['update', 'delete'], $demande)
                <div class="flex items-center gap-2">
                    @can('update', $demande)
                        <a href="{{ route('demandes-aide.edit', $demande) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100">{{ __('interface.edit') }}</a>
                    @endcan
                    @can('delete', $demande)
                        <form method="post" action="{{ route('demandes-aide.destroy', $demande) }}" onsubmit="return confirm('{{ __('interface.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-500">{{ __('interface.delete') }}</button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>

        <p class="mt-6 whitespace-pre-line text-sm text-slate-700">{{ $demande->description }}</p>
    </article>

    <section class="mt-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('interface.offers') }} ({{ $demande->offres->count() }})</h2>

        <div class="mt-4 space-y-3">
            @forelse ($demande->offres as $offre)
                <div class="rounded-md border border-slate-100 bg-slate-50 p-3">
                    <p class="text-sm font-medium text-slate-800">
                        {{ trim(($offre->utilisateur?->prenom ?? '').' '.($offre->utilisateur?->nom ?? '')) }}
                        <span class="ml-2 text-xs font-normal text-slate-400">{{ $offre->created_at?->diffForHumans() }}</span>
                    </p>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $offre->description }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('interface.no_offers') }}</p>
            @endforelse
        </div>

        @auth
            @if (! $dejaOffert && (int) auth()->id() !== (int) $demande->id_utilisateur)
                <form method="post" action="{{ route('demandes-aide.offres.store', $demande) }}" class="mt-5 space-y-3">
                    @csrf
                    <textarea name="description" rows="3" required maxlength="2000"
                              placeholder="{{ __('interface.help_offer_placeholder') }}"
                              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('description') }}</textarea>
                    @error('description') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('interface.send_help_offer') }}</button>
                </form>
            @endif
        @endauth
    </section>

    <div class="mt-6">
        <a href="{{ route('demandes-aide.index') }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">&larr; {{ __('interface.back') }}</a>
    </div>
@endsection
