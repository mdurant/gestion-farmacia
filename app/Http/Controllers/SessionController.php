<?php

namespace App\Http\Controllers;

use App\Services\SessionPolicyService;
use App\Services\SingleSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function __construct(
        private readonly SessionPolicyService $sessionPolicy,
        private readonly SingleSessionService $singleSession,
    ) {}

    public function status(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['expired' => true], 401);
        }

        $user = Auth::user();

        if ($user !== null && ! $this->singleSession->isCurrentSession($user, $request)) {
            return response()->json([
                'expired' => true,
                'reason' => 'session_superseded',
            ], 401);
        }

        if ($this->sessionPolicy->absoluteLifetimeExpired($request)) {
            return response()->json(['expired' => true, 'reason' => 'absolute_lifetime'], 401);
        }

        return response()->json($this->sessionPolicy->status($request));
    }

    public function renew(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Sesión no válida.'], 401);
        }

        $user = Auth::user();

        if ($user !== null && ! $this->singleSession->isCurrentSession($user, $request)) {
            return response()->json([
                'message' => 'Su sesión fue cerrada porque se inició sesión desde otro dispositivo.',
                'reason' => 'session_superseded',
            ], 401);
        }

        if ($this->sessionPolicy->absoluteLifetimeExpired($request)) {
            return response()->json(['message' => 'La sesión alcanzó su duración máxima.'], 401);
        }

        if (! $this->sessionPolicy->renewActivity($request)) {
            return response()->json(['message' => 'No fue posible renovar la sesión.'], 422);
        }

        return response()->json([
            'ok' => true,
            ...$this->sessionPolicy->status($request),
        ]);
    }
}
