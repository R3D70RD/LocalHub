@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold text-slate-900">Mon compte : {{ $user->nom }} {{ $user->prenom }}</h1>

    <section class="mt-6 space-y-4">
        @forelse ($publications as $publication)
            <article class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <a href="{{ route('publications.show', $publication) }}" class="text-slate-900 hover:underline">
                    {{ $publication->titre }}
                </a>
                <form method="post" action="{{ route('publications.destroy', $publication) }}"
                      onsubmit="return confirm('Supprimer cette publication ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-500">
                        Supprimer
                    </button>
                </form>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                Aucune publication.
            </p>
        @endforelse
    </section>
@endsection
