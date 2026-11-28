@extends('layouts.app')

@section('title', __('interface.my_loans'))

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $pret->outil?->titre }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('interface.loan_borrower') }} :
                {{ trim(($pret->emprunteur?->prenom ?? '').' '.($pret->emprunteur?->nom ?? '')) }}
                · {{ __('interface.loan_owner') }} :
                {{ trim(($pret->proprietaire?->prenom ?? '').' '.($pret->proprietaire?->nom ?? '')) }}
            </p>
        </div>
        <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700">
            {{ __('interface.statut_'.$pret->statut->value) }}
        </span>
    </div>

    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-slate-500">{{ __('interface.loan_tool') }}</dt>
                <dd class="mt-1 font-medium text-slate-900">
                    <a href="{{ route('outils.show', $pret->outil) }}" class="hover:underline">
                        {{ $pret->outil?->titre }}
                    </a>
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('interface.loan_period') }}</dt>
                <dd class="mt-1 font-medium text-slate-900">
                    {{ $pret->date_debut?->toDateString() }} → {{ $pret->date_fin?->toDateString() }}
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">{{ __('interface.status') }}</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ __('interface.statut_'.$pret->statut->value) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Version</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $pret->version }}</dd>
            </div>
        </dl>

        @if ($pret->message)
            <p class="mt-4 rounded-md bg-slate-50 p-3 text-sm text-slate-700">{{ $pret->message }}</p>
        @endif

        @if ($actions !== [])
            <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                @foreach ($actions as $action)
                    <form method="post" action="{{ route('prets.transition', $pret) }}">
                        @csrf
                        <input type="hidden" name="action" value="{{ $action }}">
                        <input type="hidden" name="version" value="{{ $pret->version }}">
                        <button type="submit"
                                class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                            {{ __('interface.action_'.$action) }}
                        </button>
                    </form>
                @endforeach
            </div>
        @endif

        @error('action') <p class="mt-3 text-sm text-rose-600">{{ $message }}</p> @enderror
    </section>

    @if ($peutNoter && ! $avisExistant)
        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('interface.loan_review') }}</h2>
            <form method="post" action="{{ route('prets.avis.store', $pret) }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="note" class="block text-sm font-medium text-slate-700">{{ __('interface.review_note') }}</label>
                    <select id="note" name="note" class="mt-1 rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected((int) old('note', 5) === $i)>{{ $i }} / 5</option>
                        @endfor
                    </select>
                    @error('note') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="commentaire" class="block text-sm font-medium text-slate-700">{{ __('interface.review_comment') }}</label>
                    <textarea id="commentaire" name="commentaire" rows="3"
                              class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('commentaire') }}</textarea>
                    @error('commentaire') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('interface.submit_review') }}</button>
            </form>
        </section>
    @endif

    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">{{ __('interface.chat') }}</h2>

        <div class="mt-4 space-y-3">
            @forelse ($pret->messages as $message)
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

        <form method="post" action="{{ route('prets.messages.store', $pret) }}" class="mt-5 space-y-3">
            @csrf
            <textarea name="contenu" rows="3" required maxlength="2000"
                      placeholder="{{ __('interface.message_placeholder') }}"
                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('contenu') }}</textarea>
            @error('contenu') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
            <button type="submit"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('interface.send_message') }}
            </button>
        </form>
    </section>

    <div class="mt-6">
        <a href="{{ route('prets.index') }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">
            &larr; {{ __('interface.my_loans') }}
        </a>
    </div>
@endsection
