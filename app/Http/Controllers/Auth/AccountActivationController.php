<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteActivationRequest;
use App\Http\Requests\Auth\RequestActivationOtpRequest;
use App\Http\Requests\Auth\VerifyActivationOtpRequest;
use App\Models\User;
use App\Services\UserActivationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountActivationController extends Controller
{
    public function __construct(
        private readonly UserActivationService $activationService,
    ) {}

    public function showEmailForm(): View
    {
        return view('auth.activation.email');
    }

    public function sendOtp(RequestActivationOtpRequest $request): RedirectResponse
    {
        $user = $this->activationService->findPendingUserByEmail($request->validated('email'));

        if ($user !== null) {
            $this->activationService->issueChallenge($user);
        }

        $request->session()->put('activation.email', $request->validated('email'));

        return redirect()
            ->route('activation.verify.form')
            ->with('status', 'Si su correo está registrado y pendiente de activación, recibirá un código de 6 dígitos.');
    }

    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('activation.email')) {
            return redirect()->route('activation.request');
        }

        return view('auth.activation.verify', [
            'email' => $request->session()->get('activation.email'),
        ]);
    }

    public function verifyOtp(VerifyActivationOtpRequest $request): RedirectResponse
    {
        $user = $this->activationService->findPendingUserByEmail($request->session()->get('activation.email', ''));

        if ($user === null || ! $this->activationService->verifyChallenge($user, $request->validated('code'))) {
            return back()
                ->withErrors(['code' => 'El código es incorrecto o ha expirado. Solicite uno nuevo.'])
                ->withInput();
        }

        $request->session()->put('activation.user_id', $user->id);
        $request->session()->put('activation.verified_at', now()->timestamp);

        return redirect()->route('activation.password.form');
    }

    public function showPasswordForm(Request $request): View|RedirectResponse
    {
        if (! $this->sessionIsVerified($request)) {
            return redirect()->route('activation.request');
        }

        return view('auth.activation.password');
    }

    public function complete(CompleteActivationRequest $request): RedirectResponse
    {
        if (! $this->sessionIsVerified($request)) {
            return redirect()->route('activation.request');
        }

        /** @var User|null $user */
        $user = User::query()->find($request->session()->get('activation.user_id'));

        if ($user === null || $user->isActivated()) {
            $request->session()->forget(['activation.email', 'activation.user_id', 'activation.verified_at']);

            return redirect()->route('login')->with('status', 'Su cuenta ya está activa. Puede iniciar sesión.');
        }

        $this->activationService->completeActivation($user, $request->validated('password'));

        $request->session()->forget(['activation.email', 'activation.user_id', 'activation.verified_at']);

        return redirect()
            ->route('login')
            ->with('status', 'Cuenta activada correctamente. Ya puede iniciar sesión con su contraseña.');
    }

    private function sessionIsVerified(Request $request): bool
    {
        $verifiedAt = $request->session()->get('activation.verified_at');
        $userId = $request->session()->get('activation.user_id');

        if (! $userId || ! $verifiedAt) {
            return false;
        }

        return (now()->timestamp - (int) $verifiedAt) <= 900;
    }
}
