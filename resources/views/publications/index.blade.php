@extends('layouts.app')

@section('title', __('interface.publications'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('interface.publications') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('interface.publications_intro') }}</p>
        </div>
        @auth
            <a href="{{ route('publications.create') }}"
               class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                + {{ __('interface.new_publication') }}
            </a>
        @endauth
    </div>

    <form method="get" action="{{ route('publications.index') }}"
          class="mb-6 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_1fr_2fr_auto]">
        <div>
            <label for="scope" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                {{ __('interface.scope') }}
            </label>
            <select id="scope" name="scope"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="quartier" @selected($scope === 'quartier')>{{ __('interface.scope_quartier') }}</option>
                <option value="ville" @selected($scope === 'ville')>{{ __('interface.scope_ville') }}</option>
                <option value="pays" @selected($scope === 'pays')>{{ __('interface.scope_pays') }}</option>
            </select>
        </div>

        <div>
            <label for="lang" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                {{ __('interface.language') }}
            </label>
            <select id="lang" name="lang"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                <option value="">{{ __('interface.all_languages') }}</option>
                <option value="fr" @selected($lang === 'fr')>Français</option>
                <option value="en" @selected($lang === 'en')>English</option>
            </select>
        </div>

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
        @forelse ($publications as $publication)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            <a href="{{ route('publications.show', $publication) }}" class="hover:underline">
                                {{ $publication->titre }}
                            </a>
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('interface.by_author', ['name' => trim(($publication->utilisateur?->prenom ?? '').' '.($publication->utilisateur?->nom ?? ''))]) }}
                            @if ($publication->date_creation)
                                · {{ $publication->date_creation->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                            {{ __('interface.scope_'.$publication->portee->value) }}
                        </span>
                        @if ($publication->category)
                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">
                                {{ $publication->category->nom }}
                            </span>
                        @endif
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium uppercase text-slate-600">
                            {{ $publication->lang?->value }}
                        </span>
                    </div>
                </div>

                @php($premiereImage = $publication->medias->first())
                @if ($premiereImage)
                    <img src="{{ $premiereImage->url() }}" alt="{{ $publication->titre }}"
                         class="mt-4 max-h-64 w-full rounded-md object-cover">
                @endif

                <p class="mt-3 text-sm text-slate-700">
                    {{ \Illuminate\Support\Str::limit($publication->description, 240) }}
                </p>

                <div class="mt-4 flex items-center gap-4 text-xs text-slate-500">
                    <span>👍 {{ $publication->reactions_count }}</span>
                    <span>💬 {{ $publication->commentaires_count }}</span>
                    <a href="{{ route('publications.show', $publication) }}" class="ml-auto font-medium text-indigo-600 hover:underline">
                        {{ __('interface.comments') }} →
                    </a>
                </div>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                {{ __('interface.no_publications') }}
            </p>
        @endforelse
    </section>

    @if ($publications->lastPage() > 1)
        <nav class="mt-8 flex items-center justify-between text-sm" aria-label="Pagination">
            <div class="text-slate-500">
                {{ __('interface.page_of', ['current' => $publications->currentPage(), 'last' => $publications->lastPage()]) }}
            </div>
            <div class="flex gap-2">
                @if ($publications->onFirstPage())
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&laquo;</span>
                @else
                    <a href="{{ $publications->previousPageUrl() }}"
                       class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&laquo;</a>
                @endif

                @if ($publications->hasMorePages())
                    <a href="{{ $publications->nextPageUrl() }}"
                       class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&raquo;</a>
                @else
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&raquo;</span>
                @endif
            </div>
        </nav>
    @endif
@endsection
