@extends('layouts.app')

@section('title', $annonce->titre)

@section('content')
    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $annonce->titre }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('interface.by_author', ['name' => trim(($annonce->utilisateur?->prenom ?? '').' '.($annonce->utilisateur?->nom ?? ''))]) }}
                    · {{ $annonce->created_at?->diffForHumans() }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ __('interface.scope_'.$annonce->portee->value) }}</span>
                    @if ($annonce->categorie)
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">{{ $annonce->categorie->nom }}</span>
                    @endif
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ __('interface.type_'.$annonce->type_annonce) }}</span>
                    @if ($annonce->estGratuite())
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700">{{ __('interface.free') }}</span>
                    @elseif ($annonce->prix !== null)
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700">{{ $annonce->prix }} $</span>
                    @endif
                </div>
            </div>

            @canany(['update', 'delete'], $annonce)
                <div class="flex items-center gap-2">
                    @can('update', $annonce)
                        <a href="{{ route('annonces.edit', $annonce) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100">{{ __('interface.edit') }}</a>
                    @endcan
                    @can('delete', $annonce)
                        <form method="post" action="{{ route('annonces.destroy', $annonce) }}" onsubmit="return confirm('{{ __('interface.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-500">{{ __('interface.delete') }}</button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>

        <p class="mt-6 whitespace-pre-line text-sm text-slate-700">{{ $annonce->description }}</p>
    </article>

    @auth
        @if ((int) auth()->id() !== (int) $annonce->id_utilisateur)
            <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('interface.make_offer') }}</h2>
                <form method="post" action="{{ route('annonces.offres.store', $annonce) }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="montant" class="block text-sm font-medium text-slate-700">{{ __('interface.offer_amount') }}</label>
                        <input id="montant" name="montant" type="number" step="0.01" min="0" value="{{ old('montant') }}"
                               class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        @error('montant') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-700">{{ __('interface.offer_message') }}</label>
                        <textarea id="message" name="message" rows="3" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('interface.send_offer') }}</button>
                </form>
            </section>
        @else
            <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('interface.offers') }} ({{ $annonce->offres->count() }})</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($annonce->offres as $offre)
                        <div class="rounded-md border border-slate-100 bg-slate-50 p-3">
                            <p class="text-sm font-medium text-slate-800">
                                {{ trim(($offre->utilisateur?->prenom ?? '').' '.($offre->utilisateur?->nom ?? '')) }}
                                @if ($offre->montant !== null) · {{ $offre->montant }} $ @endif
                            </p>
                            @if ($offre->message)
                                <p class="mt-1 text-sm text-slate-700">{{ $offre->message }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">{{ __('interface.no_offers') }}</p>
                    @endforelse
                </div>
            </section>
        @endif
    @endauth

    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('interface.chat') }} ({{ $annonce->messages->count() }})</h2>

        <div class="mt-4 space-y-3">
            @forelse ($annonce->messages as $message)
                <div class="rounded-md border border-slate-100 bg-slate-50 p-3">
                    <p class="text-sm font-medium text-slate-800">
                        {{ trim(($message->expediteur?->prenom ?? '').' '.($message->expediteur?->nom ?? '')) }}
                        <span class="ml-2 text-xs font-normal text-slate-400">{{ $message->created_at?->diffForHumans() }}</span>
                    </p>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $message->contenu }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('interface.no_messages') }}</p>
            @endforelse
        </div>

        @auth
            <form method="post" action="{{ route('annonces.messages.store', $annonce) }}" class="mt-5 space-y-3">
                @csrf
                <textarea name="contenu" rows="3" required maxlength="2000"
                          placeholder="{{ __('interface.message_placeholder') }}"
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('contenu') }}</textarea>
                @error('contenu') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('interface.send_message') }}</button>
            </form>
        @endauth
    </section>

    <div class="mt-6">
        <a href="{{ route('annonces.index') }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">&larr; {{ __('interface.back') }}</a>
    </div>
@endsection
