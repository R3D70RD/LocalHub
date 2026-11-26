@extends('layouts.app')

@section('title', $outil->titre)

@section('content')
    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $outil->titre }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('interface.tool_owner', ['name' => trim(($outil->proprietaire?->prenom ?? '').' '.($outil->proprietaire?->nom ?? ''))]) }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full px-2.5 py-1 font-medium {{ $outil->disponible ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $outil->disponible ? __('interface.available') : __('interface.unavailable') }}
                    </span>
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
            </div>

            @canany(['update', 'delete'], $outil)
                <div class="flex items-center gap-2">
                    @can('update', $outil)
                        <a href="{{ route('outils.edit', $outil) }}"
                           class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100">
                            {{ __('interface.edit') }}
                        </a>
                    @endcan
                    @can('delete', $outil)
                        <form method="post" action="{{ route('outils.destroy', $outil) }}"
                              onsubmit="return confirm('{{ __('interface.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-500">
                                {{ __('interface.delete') }}
                            </button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>

        @if ($outil->medias->isNotEmpty())
            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                @foreach ($outil->medias as $media)
                    <img src="{{ $media->url() }}" alt="{{ $outil->titre }}"
                         class="max-h-96 w-full rounded-md object-cover">
                @endforeach
            </div>
        @endif

        <p class="mt-6 whitespace-pre-line text-sm text-slate-700">{{ $outil->description }}</p>
    </article>

    @if ($pretActif)
        <section class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
            <p class="font-medium">{{ __('interface.tool_reserved') }}</p>
            <p class="mt-1">
                {{ __('interface.statut_'.$pretActif->statut->value) }}
                · {{ $pretActif->date_debut->toDateString() }} → {{ $pretActif->date_fin->toDateString() }}
            </p>
            @if (auth()->check() && in_array(auth()->id(), [$pretActif->id_emprunteur, $pretActif->id_proprietaire], true))
                <a href="{{ route('prets.show', $pretActif) }}" class="mt-2 inline-block font-medium text-amber-900 hover:underline">
                    {{ __('interface.my_loans') }} →
                </a>
            @endif
        </section>
    @elseif (auth()->check() && auth()->id() !== $outil->id_proprietaire && $outil->disponible && $outil->statut === 'actif')
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('interface.borrow_request') }}</h2>

            <form method="post" action="{{ route('outils.prets.store', $outil) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label for="date_debut" class="block text-sm font-medium text-slate-700">{{ __('interface.loan_start') }}</label>
                    <input id="date_debut" name="date_debut" type="date" required
                           value="{{ old('date_debut', now()->toDateString()) }}"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    @error('date_debut') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="date_fin" class="block text-sm font-medium text-slate-700">{{ __('interface.loan_end') }}</label>
                    <input id="date_fin" name="date_fin" type="date" required
                           value="{{ old('date_fin', now()->addDays(3)->toDateString()) }}"
                           class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    @error('date_fin') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="message" class="block text-sm font-medium text-slate-700">{{ __('interface.loan_message') }}</label>
                    <textarea id="message" name="message" rows="3"
                              class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    @error('pret') <p class="mb-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    <button type="submit"
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        {{ __('interface.borrow') }}
                    </button>
                </div>
            </form>
        </section>
    @elseif (! auth()->check())
        <p class="mt-6 rounded-lg border border-slate-200 bg-white p-5 text-sm text-slate-600">
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:underline">{{ __('interface.nav_login') }}</a>
            {{ __('interface.borrow_request') }}
        </p>
    @endif

    <div class="mt-6 flex items-center gap-4 text-sm">
        <a href="{{ route('outils.index') }}" class="text-slate-600 hover:text-slate-900 hover:underline">
            &larr; {{ __('interface.back') }}
        </a>
        @auth
            <a href="{{ route('prets.index') }}" class="text-slate-600 hover:text-slate-900 hover:underline">
                {{ __('interface.my_loans') }}
            </a>
        @endauth
    </div>
@endsection
