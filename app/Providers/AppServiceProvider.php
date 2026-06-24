<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\CostCenter;
use App\Models\Drug;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\User;
use App\Policies\BatchPolicy;
use App\Policies\CostCenterPolicy;
use App\Policies\DrugPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\PharmacyPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ResidentPolicy;
use App\Policies\UserPolicy;
use App\Support\AcalisMail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Batch::class, BatchPolicy::class);
        Gate::policy(CostCenter::class, CostCenterPolicy::class);
        Gate::policy(Drug::class, DrugPolicy::class);
        Gate::policy(InventoryMovement::class, InventoryMovementPolicy::class);
        Gate::policy(Pharmacy::class, PharmacyPolicy::class);
        Gate::policy(Resident::class, ResidentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('reports.internal', [ReportPolicy::class, 'viewInternal']);
        Gate::define('reports.executive', [ReportPolicy::class, 'viewExecutive']);

        RateLimiter::for('inventory-movements', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('critical-inventory', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('resident-data-gate', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('account-activation', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('account-activation-otp', function (Request $request) {
            $email = Str::lower((string) $request->session()->get('activation.email', $request->ip()));

            return Limit::perMinute(10)->by($email.'|'.$request->ip());
        });

        $this->registerAuthMailTemplates();
    }

    private function registerAuthMailTemplates(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $name = $notifiable instanceof User
                ? $notifiable->display_name
                : ($notifiable->name ?? '');

            return AcalisMail::auth(
                subject: 'Restablecer contraseña',
                headline: 'Restablezca su contraseña',
                greeting: 'Hola '.$name,
                intro: 'Recibimos una solicitud para restablecer la contraseña de su cuenta institucional en Acalis Pharma.',
                actionUrl: $url,
                actionLabel: 'Restablecer contraseña',
                footnote: 'Si usted no solicitó este cambio, ignore este correo. El enlace expira en '
                    .config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60).' minutos.',
                tone: AcalisMail::TONE_PRIMARY,
            );
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $verificationUrl) {
            $name = $notifiable instanceof User
                ? $notifiable->display_name
                : ($notifiable->name ?? '');

            return AcalisMail::auth(
                subject: 'Verificar correo electrónico',
                headline: 'Confirme su correo',
                greeting: 'Hola '.$name,
                intro: 'Confirme su dirección de correo para completar el acceso a la plataforma institucional.',
                actionUrl: $verificationUrl,
                actionLabel: 'Verificar correo',
                footnote: 'Si no creó una cuenta en Acalis Pharma, puede ignorar este mensaje.',
                tone: AcalisMail::TONE_INFO,
            );
        });
    }
}
