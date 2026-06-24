<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResidentDataAccessConfirmed
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->sessionIsValid($request)) {
            return $next($request);
        }

        if (! $request->session()->has('url.intended')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('residents.gate.show');
    }

    public static function sessionIsValid(Request $request): bool
    {
        $confirmedAt = $request->session()->get('residents.data_access_confirmed_at');

        if (! $confirmedAt) {
            return false;
        }

        $ttl = (int) config('acalis.residents.gate_ttl_minutes', 15) * 60;

        return (time() - (int) $confirmedAt) <= $ttl;
    }
}
