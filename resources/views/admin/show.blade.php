@if (auth()->user()?->role === \App\Models\User::ROLE_MANAGER)
    <a href="{{ route('admin.index') }}">Administration</a>
@endif
