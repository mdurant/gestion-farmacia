<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AccessLogService;
use App\Services\SessionPolicyService;
use App\Services\SingleSessionService;
use App\Services\TermsAcceptanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly TermsAcceptanceService $termsAcceptanceService,
        private readonly SessionPolicyService $sessionPolicy,
        private readonly SingleSessionService $singleSession,
        private readonly AccessLogService $accessLog,
    ) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'termsVersion' => config('acalis.terms.version', '1.0.0'),
            'activeSessionInfo' => session('active_session_info'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if (
            $user !== null
            && $this->singleSession->hasActiveSessionElsewhere($user)
            && ! $request->boolean('close_other_devices')
        ) {
            $activeSessionInfo = $this->accessLog->getActiveSessionInfo($user);
            $this->accessLog->storePendingLogin($request, $user);

            Auth::guard('web')->logout();

            return back()
                ->withInput($request->only('email', 'remember', 'terms_accepted'))
                ->with('confirm_close_other_devices', true)
                ->with('active_session_info', $activeSessionInfo);
        }

        return $this->completeLogin($request, $user, $request->boolean('close_other_devices'));
    }

    public function confirmCloseOtherDevices(Request $request): RedirectResponse
    {
        $pending = $this->accessLog->pullPendingLogin($request);

        if ($pending === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'La confirmación expiró. Ingrese nuevamente sus credenciales.']);
        }

        $user = User::query()->find($pending['user_id']);

        if ($user === null || ! $user->is_active || $user->isPendingActivation()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'No fue posible completar el ingreso. Intente nuevamente.']);
        }

        $previousToken = $user->current_session_id;
        if (is_string($previousToken) && $previousToken !== '') {
            $this->accessLog->recordDisconnection($user, $previousToken, 'superseded');
        }

        Auth::login($user, $pending['remember']);
        $request->session()->regenerate();

        $sessionToken = $this->singleSession->claimSession($user, $request);
        $this->accessLog->recordConnection($user, $request, $sessionToken);

        $this->termsAcceptanceService->logAcceptance($user, $pending['terms_version']);
        $this->sessionPolicy->initializeSession($request);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $sessionToken = $request->session()->get(SingleSessionService::SESSION_TOKEN_KEY);
            $this->accessLog->recordDisconnection($user, is_string($sessionToken) ? $sessionToken : null);
            $this->singleSession->releaseSession($user, $request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function completeLogin(
        LoginRequest $request,
        ?User $user,
        bool $closingOtherDevices,
    ): RedirectResponse {
        $request->session()->regenerate();

        if ($user !== null) {
            if ($closingOtherDevices && is_string($user->current_session_id) && $user->current_session_id !== '') {
                $this->accessLog->recordDisconnection($user, $user->current_session_id, 'superseded');
            }

            $sessionToken = $this->singleSession->claimSession($user, $request);
            $this->accessLog->recordConnection($user, $request, $sessionToken);

            $this->termsAcceptanceService->logAcceptance(
                $user,
                (string) config('acalis.terms.version', '1.0.0'),
            );
        }

        $this->sessionPolicy->initializeSession($request);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
