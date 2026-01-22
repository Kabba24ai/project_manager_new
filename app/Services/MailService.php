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
            // Try different table structures
            $settings = [];
            
            // Try structure: settings table with group and key columns
            if (DB::getSchemaBuilder()->hasTable('settings')) {
                $columns = DB::getSchemaBuilder()->getColumnListing('settings');
                
                if (in_array('group', $columns) && in_array('key', $columns)) {
                    // Structure: group='mail' or group='Mail Send Settings'
                    $settings = DB::table('settings')
                        ->where(function($query) {
                            $query->where('group', 'mail')
                                  ->orWhere('group', 'Mail Send Settings');
                        })
                        ->pluck('value', 'key')
                        ->toArray();
                } elseif (in_array('key', $columns)) {
                    // Structure: just key-value pairs
                    $mailKeys = ['mail_mailer', 'mail_host', 'mail_port', 'mail_username', 
                                'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name'];
                    $settings = DB::table('settings')
                        ->whereIn('key', $mailKeys)
                        ->pluck('value', 'key')
                        ->toArray();
                }
            }

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
            
            // Log loaded settings (without password)
            Log::info('Mail settings loaded', [
                'mailer' => $this->settings['mailer'],
                'host' => $this->settings['host'],
                'port' => $this->settings['port'],
                'from_address' => $this->settings['from']['address'],
                'has_username' => !empty($this->settings['username']),
                'has_password' => !empty($this->settings['password']),
            ]);
            
        } catch (\Throwable $e) {
            // Fallback to config if database fails
            Log::warning('Failed to load mail settings from database, using config', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
