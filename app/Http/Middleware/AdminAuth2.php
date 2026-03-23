<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth2
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('admin_user_id')) {
            return redirect()->route('admin.login');
        }

        $user = \App\Models\User::find(session('admin_user_id'));

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Admin.');
        }

        return $next($request);
    }
}
