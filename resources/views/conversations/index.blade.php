@extends('layouts.app')

@section('title', __('interface.conversations'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('interface.conversations') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('interface.conversations_intro') }}</p>
    </div>

    <form method="post" action="{{ route('conversations.demarrer') }}"
          class="mb-6 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        @csrf
        <div class="flex-1">
            <label for="destinataire" class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                {{ __('interface.choose_recipient') }}
            </label>
            <select id="destinataire" name="destinataire" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                @foreach ($destinataires as $destinataire)
                    <option value="{{ $destinataire->id_utilisateur }}">{{ $destinataire->prenom }} {{ $destinataire->nom }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            {{ __('interface.start_conversation') }}
        </button>
    </form>
    @error('destinataire') <p class="mb-4 text-sm text-rose-600">{{ $message }}</p> @enderror

    <section class="space-y-3">
        @forelse ($conversations as $conversation)
            @php($autre = $conversation->membres->first(fn ($membre) => (int) $membre->id_utilisateur !== (int) auth()->id()))
            @php($dernier = $conversation->messages->last())
            @php($nombreNonLus = $nonLus[$conversation->id_conversation] ?? 0)
            <a href="{{ route('conversations.show', $conversation) }}"
               class="block rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-medium text-slate-900">
                        {{ trim(($autre?->utilisateur?->prenom ?? '').' '.($autre?->utilisateur?->nom ?? '')) }}
                    </p>
                    @if ($nombreNonLus > 0)
                        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 text-xs font-semibold text-white">
                            {{ $nombreNonLus }}
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $dernier?->contenu ? \Illuminate\Support\Str::limit($dernier->contenu, 120) : __('interface.no_messages') }}
                </p>
            </a>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                {{ __('interface.no_conversations') }}
            </p>
        @endforelse
    </section>
@endsection
