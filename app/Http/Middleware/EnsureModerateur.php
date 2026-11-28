<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModerateur
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            in_array($request->user()?->role, [User::ROLE_MANAGER, User::ROLE_MODERATEUR], true),
            403,
        );

        return $next($request);
    }
}
