<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The post-SSO-disable forced re-authentication ends every session and every
 * remembered login, preserves user rows, is scoped to this app, and refuses a
 * non-database session driver. It builds its own minimal tables (the app's
 * MySQL-only migrations do not run under the test database).
 */
class SsoForceReauthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'session.driver' => 'database', 'session.connection' => null, 'session.table' => 'sessions']);
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('email')->unique(); $t->string('password')->nullable(); $t->string('status')->default('Active'); $t->rememberToken(); $t->timestamps();
        });
        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary(); $t->foreignId('user_id')->nullable(); $t->text('payload'); $t->integer('last_activity');
        });
    }

    private function seedSession(?int $userId): void
    {
        DB::table('sessions')->insert(['id' => Str::random(40), 'user_id' => $userId, 'payload' => 'x', 'last_activity' => time()]);
    }

    public function test_dry_run_reports_counts_and_changes_nothing(): void
    {
        $u = User::forceCreate(['email' => 'a@test.local', 'password' => bcrypt('x'), 'status' => 'Active', 'remember_token' => Str::random(60)]);
        $this->seedSession($u->id);
        $this->seedSession(null);

        $this->artisan('sso:force-reauth')
            ->expectsOutputToContain('active sessions: 2')
            ->expectsOutputToContain('remembered logins (remember_token set): 1')
            ->expectsOutputToContain('DRY RUN')
            ->assertExitCode(0);

        $this->assertSame(2, DB::table('sessions')->count());
        $this->assertNotNull($u->fresh()->remember_token);
    }

    public function test_apply_ends_sessions_and_remembered_logins_but_keeps_users(): void
    {
        $u = User::forceCreate(['email' => 'b@test.local', 'password' => bcrypt('x'), 'status' => 'Active', 'remember_token' => Str::random(60)]);
        $this->seedSession($u->id);

        $this->artisan('sso:force-reauth --apply')->expectsOutputToContain('APPLIED')->assertExitCode(0);

        $this->assertSame(0, DB::table('sessions')->count(), 'all sessions ended');
        $this->assertNull($u->fresh()->remember_token, 'remembered login cleared');
        $this->assertDatabaseHas('users', ['id' => $u->id, 'email' => 'b@test.local'], );
        $this->assertSame(1, User::count(), 'no user deleted');
    }

    public function test_it_refuses_a_non_database_session_driver(): void
    {
        config(['session.driver' => 'file']);
        $this->artisan('sso:force-reauth --apply')->assertExitCode(1);
    }
}
