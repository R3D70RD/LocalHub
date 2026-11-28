@extends('layouts.app')

@section('title', $rencontre->titre)

@section('content')
    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $rencontre->titre }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('interface.by_author', ['name' => trim(($rencontre->utilisateur?->prenom ?? '').' '.($rencontre->utilisateur?->nom ?? ''))]) }}
                    · {{ $rencontre->created_at?->diffForHumans() }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ __('interface.scope_'.$rencontre->portee->value) }}</span>
                    @if ($rencontre->categorie)
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">{{ $rencontre->categorie->nom }}</span>
                    @endif
                    @if ($rencontre->date_heure)
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700">{{ $rencontre->date_heure->format('Y-m-d H:i') }}</span>
                    @endif
                    @if ($rencontre->lieu)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ $rencontre->lieu }}</span>
                    @endif
                </div>
            </div>

            @canany(['update', 'delete'], $rencontre)
                <div class="flex items-center gap-2">
                    @can('update', $rencontre)
                        <a href="{{ route('rencontres.edit', $rencontre) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100">{{ __('interface.edit') }}</a>
                    @endcan
                    @can('delete', $rencontre)
                        <form method="post" action="{{ route('rencontres.destroy', $rencontre) }}" onsubmit="return confirm('{{ __('interface.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-500">{{ __('interface.delete') }}</button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>

        <p class="mt-6 whitespace-pre-line text-sm text-slate-700">{{ $rencontre->description }}</p>

        @if ($rencontre->interets)
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                @foreach ($rencontre->interets as $interet)
                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">{{ $interet }}</span>
                @endforeach
            </div>
        @endif

        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4 text-sm text-slate-600">
            <span>{{ __('interface.members') }} : {{ $inscrits }}</span>
            @if ($placesRestantes !== null)
                <span>· {{ __('interface.spots_left') }} : {{ $placesRestantes }}</span>
            @endif
        </div>
    </article>

    @auth
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @if ($membre)
                <p class="text-sm text-emerald-700">{{ __('interface.meetup_joined') }}</p>
                <form method="post" action="{{ route('rencontres.membres.destroy', $rencontre) }}" class="mt-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">{{ __('interface.leave') }}</button>
                </form>
            @else
                <form method="post" action="{{ route('rencontres.membres.store', $rencontre) }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('interface.join') }}</button>
                </form>
            @endif
        </section>
    @endauth

    <div class="mt-6">
        <a href="{{ route('rencontres.index') }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">&larr; {{ __('interface.back') }}</a>
    </div>
@endsection
