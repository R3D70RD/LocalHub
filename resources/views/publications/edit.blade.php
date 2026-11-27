@extends('layouts.app')

@section('title', __('interface.edit'))

@section('content')
    <h1 class="mb-6 text-2xl font-semibold text-slate-900">{{ __('interface.edit') }}</h1>

    @if ($publication->medias->isNotEmpty())
        <section class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-medium text-slate-700">{{ __('interface.existing_images') }}</h2>
            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                @foreach ($publication->medias as $media)
                    <div class="relative">
                        <img src="{{ $media->url() }}" alt="{{ $publication->titre }}"
                             class="h-32 w-full rounded-md object-cover">
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
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <form method="post" action="{{ route('publications.update', $publication) }}" enctype="multipart/form-data"
          class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('publications._form', ['publication' => $publication])
    </form>
@endsection
