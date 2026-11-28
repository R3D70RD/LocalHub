@extends('layouts.app')

@section('title', $evenement->titre)

@section('content')
    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $evenement->titre }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('interface.by_author', ['name' => trim(($evenement->utilisateur?->prenom ?? '').' '.($evenement->utilisateur?->nom ?? ''))]) }}
                    · {{ $evenement->created_at?->diffForHumans() }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ __('interface.scope_'.$evenement->portee->value) }}</span>
                    @if ($evenement->categorie)
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">{{ $evenement->categorie->nom }}</span>
                    @endif
                    <span class="rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700">
                        {{ $evenement->date_debut?->copy()->setTimezone($evenement->timezone_id)->format('Y-m-d H:i') }}
                        @if ($evenement->date_fin)
                            → {{ $evenement->date_fin?->copy()->setTimezone($evenement->timezone_id)->format('Y-m-d H:i') }}
                        @endif
                        ({{ $evenement->timezone_id }})
                    </span>
                    @if ($evenement->lieu)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ $evenement->lieu }}</span>
                    @endif
                </div>
            </div>

            @canany(['update', 'delete'], $evenement)
                <div class="flex items-center gap-2">
                    @can('update', $evenement)
                        <a href="{{ route('evenements.edit', $evenement) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100">{{ __('interface.edit') }}</a>
                    @endcan
                    @can('delete', $evenement)
                        <form method="post" action="{{ route('evenements.destroy', $evenement) }}" onsubmit="return confirm('{{ __('interface.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-500">{{ __('interface.delete') }}</button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>

        <p class="mt-6 whitespace-pre-line text-sm text-slate-700">{{ $evenement->description }}</p>

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4 text-sm text-slate-600">
            <span>{{ __('interface.registrations') }} : {{ $inscrits }}</span>
            @if ($placesRestantes !== null)
                <span>· {{ __('interface.spots_left') }} : {{ $placesRestantes }}</span>
            @endif
        </div>
    </article>

    @auth
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @if ($inscrit)
                <p class="text-sm text-emerald-700">{{ __('interface.registered') }}</p>
                <form method="post" action="{{ route('evenements.inscriptions.destroy', $evenement) }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ __('interface.unregister') }}</button>
                </form>
            @else
                <form method="post" action="{{ route('evenements.inscriptions.store', $evenement) }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('interface.register') }}</button>
                </form>
            @endif
        </section>
    @endauth

    <div class="mt-6">
        <a href="{{ route('evenements.index') }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">&larr; {{ __('interface.back') }}</a>
    </div>
@endsection
