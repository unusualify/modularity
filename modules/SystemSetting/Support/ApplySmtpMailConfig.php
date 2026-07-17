<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Support;

use Unusualify\Modularous\Facades\SystemSettings;

/**
 * Optionally override Laravel mail config from SystemSettings SMTP.
 *
 * Disabled by default — prefer .env MAIL_* values. Enable only when
 * MODULAROUS_SYSTEM_SETTINGS_MAIL_OVERRIDE=true and smtp.host is set.
 */
final class ApplySmtpMailConfig
{
    public function apply(): void
    {
        if (! (bool) modularousConfig('system_settings.mail_override_enabled', false)) {
            return;
        }

        $host = trim((string) SystemSettings::get('smtp.host', ''));

        if ($host === '') {
            return;
        }

        $port = SystemSettings::get('smtp.port');
        $username = SystemSettings::get('smtp.username');
        $password = SystemSettings::get('smtp.password');
        $encryption = SystemSettings::get('smtp.encryption');
        $fromAddress = SystemSettings::get('smtp.from_address');
        $fromName = SystemSettings::get('smtp.from_name');

        $previousConfig = config('mail');
        $mailerConfig = array_filter([
            'transport' => 'smtp',
            'host' => $host,
            'port' => is_numeric($port) ? (int) $port : null,
            'username' => is_string($username) && $username !== '' ? $username : null,
            'password' => is_string($password) && $password !== '' ? $password : null,
            // 'scheme' => $this->mapEncryptionToScheme($encryption),
        ], static fn ($value) => $value !== null);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => array_merge(
                (array) config('mail.mailers.smtp', []),
                $mailerConfig,
            ),
        ]);

        if (is_string($fromAddress) && $fromAddress !== '') {
            config(['mail.from.address' => $fromAddress]);
        }

        if (is_string($fromName) && $fromName !== '') {
            config(['mail.from.name' => $fromName]);
        }
    }

    /**
     * Map admin encryption values to Laravel SMTP mailer schemes.
     *
     * Laravel only accepts "smtp" and "smtps" as schemes.
     */
    private function mapEncryptionToScheme(mixed $encryption): ?string
    {
        if (! is_string($encryption) || $encryption === '') {
            return null;
        }

        return match (strtolower($encryption)) {
            'ssl', 'smtps' => 'smtps',
            'tls', 'smtp' => 'smtp',
            default => 'smtp',
        };
    }
}
