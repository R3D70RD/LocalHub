@extends('layouts.app')

@section('title', __('interface.notifications'))

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('interface.notifications') }}</h1>
        <form method="post" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-indigo-600 hover:underline">
                {{ __('interface.mark_all_read') }}
            </button>
        </form>
    </div>

    <section class="space-y-3">
        @forelse ($notifications as $notification)
            <article class="rounded-lg border p-4 shadow-sm {{ $notification->estLue() ? 'border-slate-200 bg-white' : 'border-indigo-200 bg-indigo-50' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="text-sm text-slate-700">
                        <p class="font-medium text-slate-900">{{ $notification->type }}</p>
                        <p class="mt-1">
                            @if ($notification->data['titre_outil'] ?? null)
                                {{ $notification->data['titre_outil'] }}
                            @endif
                            @if ($notification->data['statut'] ?? null)
                                · {{ __('interface.statut_'.$notification->data['statut']) }}
                            @endif
                            @if ($notification->data['apercu'] ?? null)
                                · {{ $notification->data['apercu'] }}
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                    @if (! $notification->estLue())
                        <form method="post" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            <button type="submit" class="shrink-0 text-xs font-medium text-indigo-600 hover:underline">
                                {{ __('interface.mark_read') }}
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                {{ __('interface.no_notifications') }}
            </p>
        @endforelse
    </section>

    @if ($notifications->lastPage() > 1)
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
@endsection
