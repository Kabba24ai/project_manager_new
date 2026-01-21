<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MailService
{
    protected array $settings = [];

    public function __construct()
    {
        $this->loadMailSettings();
    }

    /**
     * Load all mail credentials from the database.
     */
    private function loadMailSettings(): void
    {
        try {
            // Try to load from settings table
            $settings = DB::table('settings')
                ->where('group', 'mail')
                ->pluck('value', 'key')
                ->toArray();

            $this->settings = [
                'mailer'     => $settings['mail_mailer'] ?? config('mail.default', 'smtp'),
                'host'       => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                'port'       => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
                'username'   => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
                'password'   => $settings['mail_password'] ?? config('mail.mailers.smtp.password'),
                'encryption' => $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption', 'tls'),
                'from'       => [
                    'address' => $settings['mail_from_address'] ?? config('mail.from.address'),
                    'name'    => $settings['mail_from_name'] ?? config('mail.from.name'),
                ],
            ];
        } catch (\Throwable $e) {
            // Fallback to config if database fails
            Log::warning('Failed to load mail settings from database, using config', [
                'error' => $e->getMessage()
            ]);

            $this->settings = [
                'mailer'     => config('mail.default', 'smtp'),
                'host'       => config('mail.mailers.smtp.host'),
                'port'       => config('mail.mailers.smtp.port'),
                'username'   => config('mail.mailers.smtp.username'),
                'password'   => config('mail.mailers.smtp.password'),
                'encryption' => config('mail.mailers.smtp.encryption', 'tls'),
                'from'       => [
                    'address' => config('mail.from.address'),
                    'name'    => config('mail.from.name'),
                ],
            ];
        }
    }

    /**
     * Get all mail settings.
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Get a specific mail setting.
     */
    public function get(string $key): mixed
    {
        return $this->settings[$key] ?? null;
    }

    /**
     * Get "from" info.
     */
    public function getFrom(): array
    {
        return $this->settings['from'] ?? ['address' => '', 'name' => ''];
    }
}
