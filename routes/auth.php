<?php

use App\Http\Controllers\Auth\AccountActivationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::post('login/confirmar-otro-dispositivo', [AuthenticatedSessionController::class, 'confirmCloseOtherDevices'])
        ->middleware('throttle:6,1')
        ->name('login.confirm-other-devices');

    Route::get('activar-cuenta', [AccountActivationController::class, 'showEmailForm'])
        ->name('activation.request');

    Route::post('activar-cuenta', [AccountActivationController::class, 'sendOtp'])
        ->middleware('throttle:account-activation')
        ->name('activation.send');

    Route::get('activar-cuenta/codigo', [AccountActivationController::class, 'showVerifyForm'])
        ->name('activation.verify.form');

    Route::post('activar-cuenta/codigo', [AccountActivationController::class, 'verifyOtp'])
        ->middleware('throttle:account-activation-otp')
        ->name('activation.verify');

    Route::get('activar-cuenta/contrasena', [AccountActivationController::class, 'showPasswordForm'])
        ->name('activation.password.form');

    Route::post('activar-cuenta/contrasena', [AccountActivationController::class, 'complete'])
        ->middleware('throttle:6,1')
        ->name('activation.complete');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.store');
});

Route::middleware(['auth', 'session.single', 'session.policy'])->group(function () {
    Route::get('sesion/estado', [\App\Http\Controllers\SessionController::class, 'status'])
        ->name('session.status');

    Route::post('sesion/renovar', [\App\Http\Controllers\SessionController::class, 'renew'])
        ->name('session.renew');

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
