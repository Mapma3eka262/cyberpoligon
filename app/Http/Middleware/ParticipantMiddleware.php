<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isParticipant()) {
            return redirect('/')->with('error', 'Доступ разрешен только участникам команд.');
        }

        return $next($request);
    }
}