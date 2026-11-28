@extends('layouts.app')

@section('title', $publication->titre)

@section('content')
    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $publication->titre }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ __('interface.by_author', ['name' => trim(($publication->utilisateur?->prenom ?? '').' '.($publication->utilisateur?->nom ?? ''))]) }}
                    @if ($publication->date_creation)
                        · {{ $publication->date_creation->diffForHumans() }}
                    @endif
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">
                        {{ __('interface.scope_'.$publication->portee->value) }}
                    </span>
                    @if ($publication->category)
                        <span class="rounded-full bg-indigo-50 px-2.5 py-1 font-medium text-indigo-700">
                            {{ $publication->category->nom }}
                        </span>
                    @endif
                </div>
            </div>

            @canany(['update', 'delete'], $publication)
                <div class="flex items-center gap-2">
                    @can('update', $publication)
                        <a href="{{ route('publications.edit', $publication) }}"
                           class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100">
                            {{ __('interface.edit') }}
                        </a>
                    @endcan
                    @can('delete', $publication)
                        <form method="post" action="{{ route('publications.destroy', $publication) }}"
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

        @if ($publication->medias->isNotEmpty())
            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                @foreach ($publication->medias as $media)
                    <div class="relative">
                        <img src="{{ $media->url() }}" alt="{{ $publication->titre }}"
                             class="max-h-96 w-full rounded-md object-cover">
                        @can('update', $publication)
                            <form method="post" action="{{ route('medias.destroy', $media) }}"
                                  class="absolute right-2 top-2"
                                  onsubmit="return confirm('{{ __('interface.delete_image') }} ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-md bg-white/90 px-2 py-1 text-xs font-medium text-rose-600 shadow hover:bg-white">
                                    {{ __('interface.delete_image') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                @endforeach
            </div>
        @endif

        <p class="mt-6 whitespace-pre-line text-sm text-slate-700">{{ $publication->description }}</p>

        @include('publications._traduction')

        <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
            @auth
                @foreach (['j_aime' => '👍', 'utile' => '💡', 'merci' => '🙏'] as $type => $emoji)
                    @php($actif = in_array($type, $reactionsUtilisateur, true))
                    <form method="post" action="{{ route('publications.reactions.toggle', $publication) }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <button type="submit"
                                class="rounded-full border px-3 py-1.5 text-sm {{ $actif ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-300 text-slate-600 hover:bg-slate-100' }}">
                            {{ $emoji }} {{ __('interface.reaction_'.$type) }}
                            <span class="ml-1 font-semibold">{{ $compteursReactions[$type] ?? 0 }}</span>
                        </button>
                    </form>
                @endforeach
            @else
                <span class="text-sm text-slate-500">👍 {{ $publication->reactions->count() }}</span>
            @endauth
        </div>

        @auth
            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4 text-sm">
                @if ($suivi)
                    <form method="post" action="{{ route('publications.ne-plus-suivre', $publication) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">{{ __('interface.unfollow') }}</button>
                    </form>
                @else
                    <form method="post" action="{{ route('publications.suivre', $publication) }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">{{ __('interface.follow') }}</button>
                    </form>
                @endif

                @if ((int) auth()->id() !== (int) $publication->id_utilisateur && $publication->utilisateur)
                    @if ($bloqueAuteur)
                        <form method="post" action="{{ route('blocages.destroy', $publication->utilisateur) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">{{ __('interface.unblock_user') }}</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('blocages.store', $publication->utilisateur) }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">{{ __('interface.block_user') }}</button>
                        </form>
                    @endif
                @endif
            </div>

            <details class="mt-4 text-sm">
                <summary class="cursor-pointer text-slate-500 hover:text-slate-800">{{ __('interface.report') }}</summary>
                <form method="post" action="{{ route('signalements.store') }}" class="mt-3 space-y-3">
                    @csrf
                    <input type="hidden" name="cible_type" value="publication">
                    <input type="hidden" name="cible_id" value="{{ $publication->id_publication }}">
                    <select name="motif" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm">
                        <option value="spam">{{ __('interface.report_reason_spam') }}</option>
                        <option value="harcelement">{{ __('interface.report_reason_harcelement') }}</option>
                        <option value="contenu_illegal">{{ __('interface.report_reason_contenu_illegal') }}</option>
                        <option value="autre">{{ __('interface.report_reason_autre') }}</option>
                    </select>
                    <textarea name="description" rows="2" placeholder="{{ __('interface.report_details') }}"
                              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm"></textarea>
                    @error('motif') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('interface.send_report') }}</button>
                </form>
            </details>
        @endauth
    </article>

    <section class="mt-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">
            {{ __('interface.comments') }} ({{ $publication->commentaires->count() }})
        </h2>

        <div class="mt-4 space-y-3">
            @forelse ($publication->commentaires as $commentaire)
                <div class="rounded-md border border-slate-100 bg-slate-50 p-3">
                    <p class="text-sm font-medium text-slate-800">
                        {{ trim(($commentaire->utilisateur?->prenom ?? '').' '.($commentaire->utilisateur?->nom ?? '')) }}
                        <span class="ml-2 text-xs font-normal text-slate-400">
                            {{ $commentaire->created_at?->diffForHumans() }}
                        </span>
                    </p>
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $commentaire->contenu }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('interface.no_comments') }}</p>
            @endforelse
        </div>

        @auth
            <form method="post" action="{{ route('publications.commentaires.store', $publication) }}" class="mt-5 space-y-3">
                @csrf
                <div>
                    <label for="contenu" class="sr-only">{{ __('interface.comment_placeholder') }}</label>
                    <textarea id="contenu" name="contenu" rows="3" required maxlength="2000"
                              placeholder="{{ __('interface.comment_placeholder') }}"
                              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('contenu') }}</textarea>
                    @error('contenu') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    {{ __('interface.send_comment') }}
                </button>
            </form>
        @endauth
    </section>

    <div class="mt-6">
        <a href="{{ route('publications.index') }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">
            &larr; {{ __('interface.back') }}
        </a>
    </div>
@endsection
