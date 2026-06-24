<?php

namespace App\Support;

use Illuminate\Notifications\Messages\MailMessage;

final class AcalisMail
{
    public const TONE_PRIMARY = 'primary';

    public const TONE_SUCCESS = 'success';

    public const TONE_WARNING = 'warning';

    public const TONE_ERROR = 'error';

    public const TONE_INFO = 'info';

    /**
     * @param  list<array{label: string, value: ?string}>  $details
     */
    public static function notification(
        string $subject,
        string $headline,
        string $greeting,
        string $intro,
        array $details = [],
        ?string $actionUrl = null,
        ?string $actionLabel = 'Ver en el sistema',
        string $tone = self::TONE_PRIMARY,
        ?string $footnote = null,
        ?string $preheader = null,
    ): MailMessage {
        return (new MailMessage)
            ->subject(self::formatSubject($subject))
            ->view('mail.notification', self::payload(
                headline: $headline,
                greeting: $greeting,
                intro: $intro,
                details: $details,
                actionUrl: $actionUrl,
                actionLabel: $actionLabel,
                tone: $tone,
                footnote: $footnote,
                preheader: $preheader ?? $intro,
            ));
    }

    public static function auth(
        string $subject,
        string $headline,
        string $greeting,
        string $intro,
        string $actionUrl,
        string $actionLabel,
        ?string $footnote = null,
        string $tone = self::TONE_PRIMARY,
    ): MailMessage {
        return (new MailMessage)
            ->subject(self::formatSubject($subject))
            ->view('mail.auth.action', self::payload(
                headline: $headline,
                greeting: $greeting,
                intro: $intro,
                details: [],
                actionUrl: $actionUrl,
                actionLabel: $actionLabel,
                tone: $tone,
                footnote: $footnote,
                preheader: $intro,
            ));
    }

    public static function otp(
        string $subject,
        string $headline,
        string $greeting,
        string $intro,
        string $otpCode,
        string $actionUrl,
        ?string $footnote = null,
    ): MailMessage {
        return (new MailMessage)
            ->subject(self::formatSubject($subject))
            ->view('mail.auth.otp', array_merge(self::payload(
                headline: $headline,
                greeting: $greeting,
                intro: $intro,
                details: [],
                actionUrl: $actionUrl,
                actionLabel: 'Activar mi cuenta',
                tone: self::TONE_PRIMARY,
                footnote: $footnote,
                preheader: 'Su código de activación es '.$otpCode,
            ), [
                'otpCode' => $otpCode,
                'otpTtlMinutes' => (int) config('acalis.activation.otp_ttl_minutes', 15),
            ]));
    }

    public static function formatSubject(string $subject): string
    {
        if (str_contains($subject, 'Acalis Pharma')) {
            return $subject;
        }

        return $subject.' — Acalis Pharma';
    }

    /**
     * @param  list<array{label: string, value: ?string}>  $details
     * @return array<string, mixed>
     */
    private static function payload(
        string $headline,
        string $greeting,
        string $intro,
        array $details,
        ?string $actionUrl,
        ?string $actionLabel,
        string $tone,
        ?string $footnote,
        string $preheader,
    ): array {
        return [
            'headline' => $headline,
            'greeting' => $greeting,
            'intro' => $intro,
            'details' => array_values(array_filter(
                $details,
                fn (array $row): bool => filled($row['value'] ?? null),
            )),
            'actionUrl' => $actionUrl,
            'actionLabel' => $actionLabel,
            'tone' => $tone,
            'footnote' => $footnote,
            'preheader' => $preheader,
            'appName' => config('app.name', 'Acalis Pharma'),
            'appUrl' => config('app.url'),
        ];
    }
}
