@extends('layouts.app')

@section('title', $titre)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $titre }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $intro }}</p>
        </div>
        @auth
            <a href="{{ route($routePrefix.'.create') }}"
               class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                + {{ $config['nouveau'] }}
            </a>
        @endauth
    </div>

    <form method="get" action="{{ route($routePrefix.'.index') }}"
          class="mb-6 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm {{ $estVisiteur ? 'sm:grid-cols-[2fr_auto]' : 'sm:grid-cols-[1fr_2fr_auto]' }}">
        @unless ($estVisiteur)
            <div>
                <label for="scope" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                    {{ __('interface.scope') }}
                </label>
                <select id="scope" name="scope" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <option value="quartier" @selected($scope === 'quartier')>{{ __('interface.scope_quartier') }}</option>
                    <option value="ville" @selected($scope === 'ville')>{{ __('interface.scope_ville') }}</option>
                    <option value="pays" @selected($scope === 'pays')>{{ __('interface.scope_pays') }}</option>
                </select>
            </div>
        @endunless

        <div>
            <label for="q" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                {{ __('interface.search') }}
            </label>
            <input id="q" name="q" type="search" value="{{ request('q') }}"
                   placeholder="{{ __('interface.search_placeholder') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
        </div>

        <div class="flex items-end">
            <button type="submit"
                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                {{ __('interface.filter') }}
            </button>
        </div>
    </form>

    <section class="space-y-4">
        @forelse ($entites as $entite)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            <a href="{{ route($routePrefix.'.show', $entite) }}" class="hover:underline">
                                {{ $entite->titre }}
                            </a>
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('interface.by_author', ['name' => trim(($entite->utilisateur?->prenom ?? '').' '.($entite->utilisateur?->nom ?? ''))]) }}
                            · {{ $entite->created_at?->diffForHumans() }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                            {{ __('interface.scope_'.$entite->portee->value) }}
                        </span>
                        @if ($entite->categorie)
                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">
                                {{ $entite->categorie->nom }}
                            </span>
                        @endif
                        @foreach (($config['badges'])($entite) as $badge)
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700">{{ $badge }}</span>
                        @endforeach
                    </div>
                </div>

                <p class="mt-3 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($entite->description, 240) }}</p>

                <div class="mt-3 text-right">
                    <a href="{{ route($routePrefix.'.show', $entite) }}"
                       class="text-xs font-medium text-indigo-600 hover:underline">
                        {{ __('interface.details') }} →
                    </a>
                </div>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                {{ $config['vide'] }}
            </p>
        @endforelse
    </section>

    @if ($entites->lastPage() > 1)
        <nav class="mt-8 flex items-center justify-between text-sm" aria-label="Pagination">
            <div class="text-slate-500">
                {{ __('interface.page_of', ['current' => $entites->currentPage(), 'last' => $entites->lastPage()]) }}
            </div>
            <div class="flex gap-2">
                @if ($entites->onFirstPage())
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&laquo;</span>
                @else
                    <a href="{{ $entites->previousPageUrl() }}"
                       class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&laquo;</a>
                @endif

                @if ($entites->hasMorePages())
                    <a href="{{ $entites->nextPageUrl() }}"
                       class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&raquo;</a>
                @else
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&raquo;</span>
                @endif
            </div>
        </nav>
    @endif
@endsection
