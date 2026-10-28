@extends('layouts.app')

@section('title', 'Publications')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Publications</h1>
            <p class="mt-1 text-sm text-slate-500">
                Parcourez les publications ou utilisez la recherche.
            </p>
        </div>
        <a href="{{ route('publications.create') }}"
           class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            + Nouvelle publication
        </a>
    </div>

    <section class="mb-8 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <label for="search" class="mb-2 block text-sm font-medium text-slate-700">
            Rechercher une publication
        </label>
        <input id="search" type="search" autocomplete="off"
               placeholder="Titre, description, ville..."
               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
               data-search-url="{{ route('recherche.publications') }}"
               data-searching="Recherche en cours..."
               data-empty="Aucun résultat."
               data-count=":count résultat(s)."
               data-error="Recherche temporairement indisponible.">
        <p id="search-status" role="status" aria-live="polite"
           class="mt-2 text-sm text-slate-500"></p>
        <ul id="search-results" class="mt-3 divide-y divide-slate-100"></ul>
    </section>

    <section class="space-y-4">
        @forelse ($publications as $publication)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <h2 class="text-lg font-semibold text-slate-900">
                    <a href="{{ route('publications.show', $publication) }}" class="hover:underline">
                        {{ $publication->titre }}
                    </a>
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Par {{ $publication->utilisateur->nom }}
                </p>
                <p class="mt-3 text-sm text-slate-700">
                    {{ \Illuminate\Support\Str::limit($publication->description, 200) }}
                </p>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                Aucune publication.
            </p>
        @endforelse
    </section>

    <div class="mt-8">
        {{ $publications->links() }}
    </div>
@endsection

