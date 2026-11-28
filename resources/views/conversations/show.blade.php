@extends('layouts.app')

@section('title', __('interface.conversations'))

@section('content')
    @php($autre = $conversation->membres->first(fn ($membre) => (int) $membre->id_utilisateur !== (int) auth()->id()))

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">
            {{ __('interface.conversation_with', ['name' => trim(($autre?->utilisateur?->prenom ?? '').' '.($autre?->utilisateur?->nom ?? ''))]) }}
        </h1>
        <a href="{{ route('conversations.index') }}" class="text-sm text-slate-600 hover:underline">&larr; {{ __('interface.back') }}</a>
    </div>

    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="space-y-3">
            @forelse ($conversation->messages as $message)
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

        <form method="post" action="{{ route('conversations.messages.store', $conversation) }}" class="mt-5 space-y-3">
            @csrf
            <textarea name="contenu" rows="3" required maxlength="2000"
                      placeholder="{{ __('interface.message_placeholder') }}"
                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">{{ old('contenu') }}</textarea>
            @error('contenu') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
            <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('interface.send_message') }}
            </button>
        </form>
    </section>
@endsection
