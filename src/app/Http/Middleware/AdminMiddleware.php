<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // ログインしていない or 管理者じゃないなら403エラー
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, '管理者のみアクセス可能です');
        }

        return $next($request);
    }
}
