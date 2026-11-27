@extends('layouts.app')

@section('title', __('interface.my_loans'))

@section('content')
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('interface.my_loans') }}</h1>
        </div>
        <a href="{{ route('outils.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
            {{ __('interface.tools') }} →
        </a>
    </div>

    <section class="space-y-4">
        @forelse ($prets as $pret)
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            <a href="{{ route('prets.show', $pret) }}" class="hover:underline">
                                {{ $pret->outil?->titre }}
                            </a>
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('interface.loan_borrower') }} :
                            {{ trim(($pret->emprunteur?->prenom ?? '').' '.($pret->emprunteur?->nom ?? '')) }}
                            · {{ __('interface.loan_owner') }} :
                            {{ trim(($pret->proprietaire?->prenom ?? '').' '.($pret->proprietaire?->nom ?? '')) }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                        {{ __('interface.statut_'.$pret->statut->value) }}
                    </span>
                </div>
                <p class="mt-3 text-sm text-slate-600">
                    {{ __('interface.loan_period') }} : {{ $pret->date_debut?->toDateString() }} → {{ $pret->date_fin?->toDateString() }}
                </p>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                {{ __('interface.no_loans') }}
            </p>
        @endforelse
    </section>

    @if ($prets->lastPage() > 1)
        <div class="mt-6">{{ $prets->links() }}</div>
    @endif
@endsection
