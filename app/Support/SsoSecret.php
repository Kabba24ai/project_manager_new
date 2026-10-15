<?php

namespace App\Support;

/**
 * The SSO signing secret shared with Kabba, or null when SSO must stay off.
 *
 * Read from configuration (config services.sso.shared_secret), so it survives
 * `php artisan config:cache`; env() in application code does not. There is no
 * fallback to APP_KEY: that key was published, and signing SSO with it let
 * anyone forge a login. Refused (SSO off) when the secret is missing, under 32
 * characters, or equal to this app's APP_KEY or any APP_PREVIOUS_KEYS entry.
 */
final class SsoSecret
{
    public const MIN_LENGTH = 32;

    public static function resolve(): ?string
    {
        $secret = trim((string) config('services.sso.shared_secret'));
        if (strlen($secret) < self::MIN_LENGTH) {
            return null;
        }

        $appKeys = array_filter(array_merge([config('app.key')], (array) config('app.previous_keys', [])), 'is_string');
        foreach ($appKeys as $key) {
            $key = trim($key);
            if ($key !== '' && (hash_equals($key, $secret) || hash_equals(preg_replace('/^base64:/', '', $key), $secret))) {
                return null;
            }
        }

        return $secret;
    }

    public static function configured(): bool
    {
        return self::resolve() !== null;
    }
}
