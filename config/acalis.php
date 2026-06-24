<?php

return [

    'app' => [
        'name' => env('ACALIS_APP_NAME', 'Acalis Pharma'),
        'version' => env('ACALIS_APP_VERSION', '1.0.0'),
    ],

    'activation' => [
        'otp_length' => 6,
        'otp_ttl_minutes' => (int) env('ACTIVATION_OTP_TTL', 15),
        'max_attempts' => (int) env('ACTIVATION_OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown_seconds' => (int) env('ACTIVATION_OTP_RESEND_COOLDOWN', 60),
    ],

    'residents' => [
        'gate_ttl_minutes' => (int) env('RESIDENTS_GATE_TTL', 15),
    ],

    'terms' => [
        'version' => env('ACALIS_TERMS_VERSION', '1.0.0'),
    ],

    'session' => [
        'absolute_lifetime_minutes' => (int) env('ACALIS_SESSION_ABSOLUTE_MINUTES', 60),
        'idle_minutes' => (int) env('ACALIS_SESSION_IDLE_MINUTES', 15),
        'warning_countdown_seconds' => (int) env('ACALIS_SESSION_WARNING_SECONDS', 60),
        'single_device' => (bool) env('ACALIS_SESSION_SINGLE_DEVICE', true),
    ],

    'mail' => [
        'notifications_address' => env('ACALIS_EMAIL_NOTIFICATIONS'),
        'notifications_password' => env('ACALIS_EMAIL_NOTIFICATIONS_PASSWORD'),
    ],

    'demo' => [
        'enabled' => (bool) env('ACALIS_DEMO_MODE', env('APP_ENV') === 'local'),
        'notification_email' => env('ACALIS_DEMO_NOTIFICATION_EMAIL', env('ACALIS_EMAIL_NOTIFICATIONS', 'acalisnotificaciones@gmail.com')),
    ],

    'inventory' => [
        'high_value_waste_threshold' => (int) env('ACALIS_HIGH_VALUE_WASTE_THRESHOLD', 50000),
    ],

];
