<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'LocalHub'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3">
            <a href="{{ url('/') }}" class="text-lg font-semibold text-slate-900">
                {{ config('app.name', 'LocalHub') }}
            </a>
            <nav class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('publications.index') }}"
                   class="rounded-md px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                    {{ __('interface.nav_publications') }}
                </a>
                @if (Route::has('outils.index'))
                    <a href="{{ route('outils.index') }}"
                       class="rounded-md px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                        {{ __('interface.nav_tools') }}
                    </a>
                @endif

                @auth
                    @if (auth()->user()?->role === \App\Models\User::ROLE_MANAGER)
                        <a href="{{ route('admin.index') }}"
                           class="rounded-md bg-amber-500 px-3 py-1.5 font-medium text-white hover:bg-amber-600">
                            {{ __('interface.nav_admin') }}
                        </a>
                    @endif
                    <a href="{{ route('notifications.index') }}"
                       class="relative rounded-md px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                        {{ __('interface.notifications') }}
                        @php($nonLues = auth()->user()->unreadNotifications()->count())
                        @if ($nonLues > 0)
                            <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full bg-rose-600 px-1.5 text-[10px] font-semibold text-white">
                                {{ $nonLues }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('account.show') }}"
                       class="rounded-md px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                        {{ __('interface.nav_account') }}
                    </a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-md px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                            {{ __('interface.nav_logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="rounded-md px-3 py-1.5 text-slate-600 hover:bg-slate-100 hover:text-slate-900">
                        {{ __('interface.nav_login') }}
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded-md bg-indigo-600 px-3 py-1.5 font-medium text-white hover:bg-indigo-700">
                        {{ __('interface.nav_register') }}
                    </a>
                @endauth

                <form method="post" action="{{ route('locale.update') }}" class="flex items-center gap-1">
                    @csrf
                    <label for="locale" class="sr-only">{{ __('interface.language') }}</label>
                    <select id="locale" name="locale"
                            class="rounded-md border border-slate-300 px-2 py-1 text-xs shadow-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                        <option value="fr" @selected(app()->getLocale() === 'fr')>FR</option>
                        <option value="en" @selected(app()->getLocale() === 'en')>EN</option>
                    </select>
                    <button type="submit"
                            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100">
                        {{ __('interface.apply') }}
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
