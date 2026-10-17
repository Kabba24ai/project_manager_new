<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Forced re-authentication for Project Manager, for use after SSO is disabled:
 * ends every active web session and every "remember me" cookie, so any access
 * established through the old SSO implementation (which verified with the
 * published APP_KEY) can no longer be used. Users sign in again with a password.
 *
 *   (default)  read-only: how many sessions and remembered logins would end
 *   --apply    end them, in one transaction
 *
 * Scoped to this application only. It never deletes a user, project or any
 * other application data: it clears the ephemeral sessions table and rotates
 * each user's remember_token. Counts only; no session id or token is printed.
 * Requires the database session driver (Project Manager's default); with any
 * other driver it refuses and asks for the matching method.
 */
class SsoForceReauth extends Command
{
    protected $signature = 'sso:force-reauth {--apply : End the sessions and remembered logins}';

    protected $description = 'End all Project Manager sessions and remembered logins (post-SSO-disable). Counts only.';

    public function handle(): int
    {
        if (config('session.driver') !== 'database') {
            $this->error('Session driver is "'.config('session.driver').'", not database. Use the method matching that driver; this command is not safe for it.');

            return self::FAILURE;
        }

        $table = config('session.table', 'sessions');
        $connection = config('session.connection');
        if (! Schema::connection($connection)->hasTable($table)) {
            $this->error("The sessions table '{$table}' does not exist.");

            return self::FAILURE;
        }

        $sessions = DB::connection($connection)->table($table)->count();
        $remembered = Schema::hasColumn('users', 'remember_token')
            ? DB::table('users')->whereNotNull('remember_token')->count()
            : 0;

        $this->line('== Project Manager forced re-authentication');
        $this->line('active sessions: '.$sessions);
        $this->line('remembered logins (remember_token set): '.$remembered);

        if (! $this->option('apply')) {
            $this->line('DRY RUN — nothing changed. Re-run with --apply to end them.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($connection, $table) {
            DB::connection($connection)->table($table)->delete();
            if (Schema::hasColumn('users', 'remember_token')) {
                // Rotate each remember_token so existing cookies no longer match.
                DB::table('users')->whereNotNull('remember_token')->update(['remember_token' => null]);
            }
        });

        $this->line('APPLIED — all sessions ended and remembered logins cleared. Users will sign in with a password.');
        logger()->warning('[SSO] Project Manager forced re-authentication applied', ['sessions_ended' => $sessions, 'remembered_cleared' => $remembered]);

        return self::SUCCESS;
    }
}
