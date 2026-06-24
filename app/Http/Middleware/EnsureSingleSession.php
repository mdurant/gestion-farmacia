<?php

namespace App\Http\Middleware;

use App\Services\AccessLogService;
use App\Support\LoginUrl;
use App\Services\SingleSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function __construct(
        private readonly SingleSessionService $singleSession,
        private readonly AccessLogService $accessLog,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $this->singleSession->isEnabled()) {
            return $next($request);
        }

        if ($this->singleSession->isCurrentSession($user, $request)) {
            return $next($request);
        }

        return $this->terminateSupersededSession($request);
    }

    private function terminateSupersededSession(Request $request): Response
    {
        $user = $request->user();
        $sessionToken = $request->session()->get(SingleSessionService::SESSION_TOKEN_KEY);

        if ($user !== null) {
            $this->accessLog->recordDisconnection(
                $user,
                is_string($sessionToken) ? $sessionToken : null,
                'superseded',
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Su sesión fue cerrada porque se inició sesión desde otro dispositivo.';

        if ($request->expectsJson()) {
            return response()->json([
                'expired' => true,
                'reason' => 'session_superseded',
                'message' => $message,
            ], 401);
        }

        return redirect()
            ->to(LoginUrl::to())
            ->with('status', $message);
    }
}
