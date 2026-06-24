<?php

namespace App\Http\Middleware;

use App\Support\LoginUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->is_active === false) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->to(LoginUrl::to())
                ->withErrors(['email' => 'Su cuenta ha sido desactivada. Contacte al administrador.']);
        }

        return $next($request);
    }
}
