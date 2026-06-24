<?php

namespace App\Http\Controllers;

use App\Enums\ResidentAccessAction;
use App\Services\ResidentAccessLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ResidentDataAccessController extends Controller
{
    public function __construct(
        private readonly ResidentAccessLogService $accessLogService,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', \App\Models\Resident::class);

        if (\App\Http\Middleware\EnsureResidentDataAccessConfirmed::sessionIsValid($request)) {
            return redirect()->intended(route('residents.index'));
        }

        return view('residents.data-access-gate', [
            'intendedUrl' => $request->session()->get('url.intended', route('residents.index')),
            'gateTtlMinutes' => (int) config('acalis.residents.gate_ttl_minutes', 15),
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', \App\Models\Resident::class);

        $validated = $request->validate([
            'password' => ['required', 'string'],
            'disclaimer_accepted' => ['accepted'],
        ], [
            'password.required' => 'Debe ingresar su contraseña institucional para continuar.',
            'disclaimer_accepted.accepted' => 'Debe confirmar que ha leído el aviso de protección de datos.',
        ]);

        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $validated['password'],
        ])) {
            $this->accessLogService->logModuleAccess(
                ResidentAccessAction::ModuleAccessDenied,
                ['reason' => 'invalid_password'],
            );

            return back()
                ->withErrors(['password' => 'La contraseña ingresada no es correcta. Verifique e intente nuevamente.'])
                ->withInput($request->except('password'));
        }

        $request->session()->put('residents.data_access_confirmed_at', time());

        $this->accessLogService->logModuleAccess(
            ResidentAccessAction::ModuleAccessGranted,
            [
                'disclaimer_version' => 'ley-21719-v1',
                'gate_ttl_minutes' => config('acalis.residents.gate_ttl_minutes', 15),
            ],
        );

        return redirect()
            ->intended(route('residents.index'))
            ->with('status', 'Acceso al módulo de residentes autorizado. Recuerde que toda actividad queda auditada.');
    }
}
