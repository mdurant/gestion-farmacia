<?php

namespace App\Http\Middleware;

use App\Services\AccessLogService;
use App\Services\SessionPolicyService;
use App\Services\SingleSessionService;
use App\Support\LoginUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionPolicy
{
    public function __construct(
        private readonly SessionPolicyService $sessionPolicy,
        private readonly AccessLogService $accessLog,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($this->sessionPolicy->absoluteLifetimeExpired($request)) {
            return $this->terminateSession($request, 'Su sesión alcanzó el límite de 60 minutos. Inicie sesión nuevamente.');
        }

        if ($this->sessionPolicy->idleExpired($request)) {
            return $this->terminateSession($request, 'Su sesión se cerró por inactividad. Inicie sesión nuevamente.');
        }

        if (! $request->routeIs('session.*')) {
            $this->sessionPolicy->touchActivity($request);
        }

        return $next($request);
    }

    private function terminateSession(Request $request, string $message): Response
    {
        $user = $request->user();
        $sessionToken = $request->session()->get(SingleSessionService::SESSION_TOKEN_KEY);

        if ($user !== null) {
            $this->accessLog->recordDisconnection(
                $user,
                is_string($sessionToken) ? $sessionToken : null,
                'timeout',
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()
            ->to(LoginUrl::to())
            ->with('status', $message);
    }
}
