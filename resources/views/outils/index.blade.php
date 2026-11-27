@extends('layouts.app')

@section('title', __('interface.tools'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('interface.tools') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('interface.tools_intro') }}</p>
        </div>
        @auth
            <a href="{{ route('outils.create') }}"
               class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                + {{ __('interface.new_tool') }}
            </a>
        @endauth
    </div>

    <form method="get" action="{{ route('outils.index') }}"
          class="mb-6 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_1fr_2fr_auto]">
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
        <div>
            <label for="lang" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                {{ __('interface.language') }}
            </label>
            <select id="lang" name="lang" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
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

    <section class="grid gap-4 sm:grid-cols-2">
        @forelse ($outils as $outil)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                @php($image = $outil->medias->first())
                @if ($image)
                    <img src="{{ $image->url() }}" alt="{{ $outil->titre }}"
                         class="mb-4 h-40 w-full rounded-md object-cover">
                @endif
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-900">
                        <a href="{{ route('outils.show', $outil) }}" class="hover:underline">{{ $outil->titre }}</a>
                    </h2>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $outil->disponible ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $outil->disponible ? __('interface.available') : __('interface.unavailable') }}
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('interface.tool_owner', ['name' => trim(($outil->proprietaire?->prenom ?? '').' '.($outil->proprietaire?->nom ?? ''))]) }}
                </p>
                <p class="mt-2 text-sm text-slate-700">{{ \Illuminate\Support\Str::limit($outil->description, 140) }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                        {{ __('interface.condition_'.$outil->etat) }}
                    </span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                        {{ __('interface.scope_'.$outil->portee->value) }}
                    </span>
                    @if ($outil->caution)
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700">
                            {{ __('interface.tool_deposit') }} : {{ $outil->caution }} $
                        </span>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500 sm:col-span-2">
                {{ __('interface.no_tools') }}
            </p>
        @endforelse
    </section>

    @if ($outils->lastPage() > 1)
        <nav class="mt-8 flex items-center justify-between text-sm" aria-label="Pagination">
            <div class="text-slate-500">
                {{ __('interface.page_of', ['current' => $outils->currentPage(), 'last' => $outils->lastPage()]) }}
            </div>
            <div class="flex gap-2">
                @if ($outils->onFirstPage())
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&laquo;</span>
                @else
                    <a href="{{ $outils->previousPageUrl() }}"
                       class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&laquo;</a>
                @endif
                @if ($outils->hasMorePages())
                    <a href="{{ $outils->nextPageUrl() }}"
                       class="rounded-md border border-slate-300 px-3 py-1 text-slate-700 hover:bg-slate-100">&raquo;</a>
                @else
                    <span class="rounded-md border border-slate-200 px-3 py-1 text-slate-400">&raquo;</span>
                @endif
            </div>
        </nav>
    @endif
@endsection
