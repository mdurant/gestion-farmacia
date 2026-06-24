<?php

namespace Tests\Feature;

use Tests\TestCase;

class MailConfigurationTest extends TestCase
{
    public function test_gmail_mailer_is_registered_with_expected_defaults(): void
    {
        $this->assertArrayHasKey('gmail', config('mail.mailers'));
        $this->assertSame('smtp', config('mail.mailers.gmail.transport'));
        $this->assertSame('smtp.gmail.com', config('mail.mailers.gmail.host'));
        $this->assertSame(587, (int) config('mail.mailers.gmail.port'));
    }

    public function test_acalis_mail_settings_are_exposed(): void
    {
        $this->assertArrayHasKey('mail', config('acalis'));
        $this->assertArrayHasKey('notifications_address', config('acalis.mail'));
        $this->assertArrayHasKey('notifications_password', config('acalis.mail'));
    }
}
