@extends('layouts.app')

@section('title', $titre)

@section('content')
    <div class="mx-auto max-w-2xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold text-slate-900">{{ $titre }}</h1>

        <form method="post" action="{{ $action }}" class="mt-6 space-y-5">
            @csrf
            @if ($methode !== 'POST')
                @method($methode)
            @endif

            @include($formPartial, ['entite' => $entite])

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    {{ __('interface.save') }}
                </button>
                <a href="{{ $retour }}" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">
                    {{ __('interface.cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection
