<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

final class DemoAccounts
{
    /** @var list<string> */
    private const LEGACY_EMAILS = [
        'admin@acalis-pharma.cl',
        'director@acalis-pharma.cl',
        'jefe@acalis-pharma.cl',
        'tens@acalis-pharma.cl',
    ];

    public static function notificationInbox(): string
    {
        return (string) config(
            'acalis.demo.notification_email',
            config('acalis.mail.notifications_address', 'acalisnotificaciones@gmail.com'),
        );
    }

    public static function isEnabled(): bool
    {
        return (bool) config('acalis.demo.enabled', false);
    }

    public static function isDemoUser(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return in_array(strtolower((string) $user->email), self::loginEmails(), true);
    }

    /** @return list<string> */
    public static function legacyEmails(): array
    {
        return self::LEGACY_EMAILS;
    }

    /** @return list<string> */
    public static function loginEmails(): array
    {
        return array_map(
            fn (array $account): string => strtolower($account['email']),
            self::definitions(),
        );
    }

    /**
     * Cuentas mostradas en el panel de login local.
     *
     * @return list<array{name: string, email: string, role: string}>
     */
    public static function loginPanel(): array
    {
        return array_map(
            fn (array $account): array => [
                'name' => trim("{$account['first_name']} {$account['last_name']}"),
                'email' => $account['email'],
                'role' => $account['role']->label(),
            ],
            self::definitions(),
        );
    }

    /**
     * @return list<array{email: string, role: UserRole, first_name: string, last_name: string, rut: string}>
     */
    public static function seederRecords(): array
    {
        return self::definitions();
    }

    /** @return list<array{email: string, role: UserRole, first_name: string, last_name: string, rut: string}> */
    private static function definitions(): array
    {
        return [
            [
                'email' => self::loginEmail('admin'),
                'role' => UserRole::Admin,
                'first_name' => 'Administrador',
                'last_name' => 'Sistema',
                'rut' => '11.111.111-1',
            ],
            [
                'email' => self::loginEmail('director'),
                'role' => UserRole::MedicalDirector,
                'first_name' => 'Carlos',
                'last_name' => 'Muñoz',
                'rut' => '22.222.222-2',
            ],
            [
                'email' => self::loginEmail('jefe'),
                'role' => UserRole::HeadNurse,
                'first_name' => 'Patricia',
                'last_name' => 'López',
                'rut' => '33.333.333-3',
            ],
            [
                'email' => self::loginEmail('tens'),
                'role' => UserRole::NursingTechnician,
                'first_name' => 'Andrea',
                'last_name' => 'Rojas',
                'rut' => '44.444.444-4',
            ],
        ];
    }

    private static function loginEmail(string $suffix): string
    {
        $inbox = strtolower(self::notificationInbox());
        [$local, $domain] = explode('@', $inbox, 2);

        return "{$local}+{$suffix}@{$domain}";
    }
}
