<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptainMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isCaptain()) {
            return redirect('/')->with('error', 'Доступ разрешен только капитанам команд.');
        }

        return $next($request);
    }
}