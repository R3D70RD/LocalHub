@extends('layouts.app')

@section('title', __('interface.moderation'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('interface.moderation_queue') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('interface.moderation_intro') }}</p>
    </div>

    <section class="space-y-4">
        @forelse ($signalements as $signalement)
            @php($cible = $signalement->cible())
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">
                            #{{ $signalement->id_signalement }} · {{ $signalement->cible_type }} #{{ $signalement->cible_id }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('interface.report_by', ['name' => trim(($signalement->utilisateur?->prenom ?? '').' '.($signalement->utilisateur?->nom ?? ''))]) }}
                            · {{ $signalement->created_at?->diffForHumans() }}
                        </p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                        {{ __('interface.report_reason_'.$signalement->motif) }}
                    </span>
                </div>

                @if ($signalement->description)
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $signalement->description }}</p>
                @endif

                @if ($cible)
                    <p class="mt-2 text-sm text-slate-500">
                        {{ __('interface.report_target') }} :
                        <span class="font-medium text-slate-800">{{ $cible->titre ?? $cible->nom ?? ('#'.$signalement->cible_id) }}</span>
                    </p>
                @endif

                <form method="post" action="{{ route('moderation.traiter', $signalement) }}"
                      class="mt-4 grid gap-3 sm:grid-cols-[auto_1fr_auto]">
                    @csrf
                    <select name="action" class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        <option value="rejeter">{{ __('interface.moderation_action_rejeter') }}</option>
                        <option value="masquer">{{ __('interface.moderation_action_masquer') }}</option>
                        <option value="avertir">{{ __('interface.moderation_action_avertir') }}</option>
                        <option value="supprimer">{{ __('interface.moderation_action_supprimer') }}</option>
                    </select>
                    <input name="note" placeholder="{{ __('interface.moderation_note') }}"
                           class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                    <button type="submit"
                            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        {{ __('interface.moderate') }}
                    </button>
                </form>
                @error('action') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                {{ __('interface.no_reports') }}
            </p>
        @endforelse
    </section>

    @if ($signalements->lastPage() > 1)
        <div class="mt-6">{{ $signalements->links() }}</div>
    @endif
@endsection
