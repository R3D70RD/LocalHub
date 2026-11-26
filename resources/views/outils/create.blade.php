@extends('layouts.app')

@section('title', __('interface.new_tool'))

@section('content')
    <h1 class="mb-6 text-2xl font-semibold text-slate-900">{{ __('interface.new_tool') }}</h1>

    <form method="post" action="{{ route('outils.store') }}" enctype="multipart/form-data"
          class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @include('outils._form', ['outil' => null])
    </form>
@endsection
