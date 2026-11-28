@extends('layouts.app')

@section('title', __('interface.feed'))

@section('content')
    @php
        $types = [
            'publication' => __('interface.nav_publications'),
            'outil' => __('interface.nav_tools'),
            'demande_aide' => __('interface.help_requests'),
            'annonce' => __('interface.market'),
            'evenement' => __('interface.events'),
            'rencontre' => __('interface.meetups'),
        ];
        $routes = [
            'publication' => 'publications.show',
            'outil' => 'outils.show',
            'demande_aide' => 'demandes-aide.show',
            'annonce' => 'annonces.show',
            'evenement' => 'evenements.show',
            'rencontre' => 'rencontres.show',
        ];
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('interface.feed') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('interface.feed_intro') }}</p>
    </div>

    @if ($estVisiteur)
        <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
            <p>{{ __('interface.guest_country_only') }}</p>
        </div>
    @endif

    <form method="get" action="{{ route('home') }}"
          class="mb-6 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm {{ $estVisiteur ? 'sm:grid-cols-[2fr_auto]' : 'sm:grid-cols-[1fr_2fr_auto]' }}">
        @unless ($estVisiteur)
            <div>
                <label for="scope" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('interface.scope') }}</label>
                <select id="scope" name="scope" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <option value="quartier" @selected($scope === 'quartier')>{{ __('interface.scope_quartier') }}</option>
                    <option value="ville" @selected($scope === 'ville')>{{ __('interface.scope_ville') }}</option>
                    <option value="pays" @selected($scope === 'pays')>{{ __('interface.scope_pays') }}</option>
                </select>
            </div>
        @endunless
        <div>
            <label for="q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('interface.search') }}</label>
            <input id="q" name="q" type="search" value="{{ request('q') }}"
                   placeholder="{{ __('interface.search_placeholder') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">{{ __('interface.filter') }}</button>
        </div>
    </form>

    <section class="space-y-4">
        @forelse ($fil as $item)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            <a href="{{ route($routes[$item->type], $item->id) }}" class="hover:underline">{{ $item->titre }}</a>
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('interface.by_author', ['name' => trim(($item->auteur?->prenom ?? '').' '.($item->auteur?->nom ?? ''))]) }}
                            · {{ \Illuminate\Support\Carbon::parse($item->date_publication)->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">{{ $types[$item->type] }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ __('interface.scope_'.$item->portee) }}</span>
                        @if ($item->categorie)
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ $item->categorie->nom }}</span>
                        @endif
                    </div>
                </div>

                <p class="mt-3 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($item->description, 220) }}</p>

                <div class="mt-3 text-right">
                    <a href="{{ route($routes[$item->type], $item->id) }}" class="text-xs font-medium text-indigo-600 hover:underline">
                        {{ __('interface.details') }} →
                    </a>
                </div>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">{{ __('interface.no_feed') }}</p>
        @endforelse
    </section>

    @if ($fil->lastPage() > 1)
        <nav class="mt-8 flex items-center justify-between text-sm" aria-label="Pagination">
            <div class="text-slate-500">{{ __('interface.page_of', ['current' => $fil->currentPage(), 'last' => $fil->lastPage()]) }}</div>
            <div class="flex gap-2">
                @if ($fil->onFirstPage())
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&laquo;</span>
                @else
                    <a href="{{ $fil->previousPageUrl() }}" class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&laquo;</a>
                @endif

                @if ($fil->hasMorePages())
                    <a href="{{ $fil->nextPageUrl() }}" class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&raquo;</a>
                @else
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&raquo;</span>
                @endif
            </div>
        </nav>
    @endif
@endsection
