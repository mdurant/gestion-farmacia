<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Services\SessionPolicyService;
use App\Services\SingleSessionService;
use App\Support\LoginUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SessionDemoController extends Controller
{
    public function __construct(
        private readonly SingleSessionService $singleSession,
        private readonly SessionPolicyService $sessionPolicy,
    ) {}

    public function show(Request $request): View
    {
        return view('dev.session-demo', [
            'user' => $request->user(),
            'deviceToken' => $request->session()->get(SingleSessionService::SESSION_TOKEN_KEY),
            'serverToken' => $request->user()?->current_session_id,
            'tokensMatch' => $request->user() !== null
                && $this->singleSession->isCurrentSession($request->user(), $request),
            'singleDeviceEnabled' => $this->singleSession->isEnabled(),
            'idleMinutes' => config('acalis.session.idle_minutes', 15),
            'warningSeconds' => config('acalis.session.warning_countdown_seconds', 60),
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'authenticated' => $user !== null,
            'single_device' => $this->singleSession->isEnabled(),
            'device_token' => $request->session()->get(SingleSessionService::SESSION_TOKEN_KEY),
            'server_token' => $user?->current_session_id,
            'is_current' => $user !== null && $this->singleSession->isCurrentSession($user, $request),
            'session_policy' => $this->sessionPolicy->status($request),
        ]);
    }

    /** Simula que otro equipo inició sesión (sin borrar la sesión local). */
    public function simulateOtherDevice(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->to(LoginUrl::to());
        }

        $user->newQuery()->whereKey($user->getKey())->update([
            'current_session_id' => Str::random(40),
        ]);

        return redirect()
            ->route('dev.session-demo.index')
            ->with('status', 'Conflicto simulado: otro dispositivo tomó la sesión. En unos segundos debe aparecer el modal en el portal.');
    }
}
