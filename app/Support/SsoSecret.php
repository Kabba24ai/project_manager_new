<?php

namespace App\Support;

/**
 * The SSO signing secret shared with Kabba, or null when SSO must stay off.
 *
 * Read from configuration, so it survives `php artisan config:cache`; env() in
 * application code does not. SSO is OFF unless a master switch
 * (config services.sso.enabled, env SSO_ENABLED) is explicitly true AND a
 * usable secret is present; the switch defaults OFF and overrides any leftover
 * SSO_SHARED_SECRET. There is no fallback to APP_KEY (that key was published).
 * The secret is also refused when missing, under 32 characters, or equal to
 * this app's APP_KEY or any APP_PREVIOUS_KEYS entry.
 */
final class SsoSecret
{
    public const MIN_LENGTH = 32;

    /** Master switch. Defaults OFF; must be explicitly true for SSO to run at all. */
    public static function enabled(): bool
    {
        return config('services.sso.enabled') === true;
    }

    public static function resolve(): ?string
    {
        if (! self::enabled()) {
            return null;
        }

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
