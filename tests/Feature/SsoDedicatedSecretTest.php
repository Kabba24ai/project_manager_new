<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SsoSecret;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Project Manager SSO verifies with a dedicated secret shared with Kabba, read
 * through configuration, and never falls back to APP_KEY (which was published).
 * Without a valid secret SSO is off and password login still works. A token in
 * the existing format, signed with the new secret, still logs in.
 *
 * This test builds a minimal users table itself (an array-driver sqlite memory
 * connection) rather than using the app's MySQL-only migration set, so it is
 * independent of unrelated schema.
 */
class SsoDedicatedSecretTest extends TestCase
{
    private string $secret;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->unique();
            $t->string('password')->nullable();
            $t->string('status')->default('Active');
            $t->rememberToken();
            $t->timestamps();
        });
        $this->secret = Str::random(48);
        // These exercise SSO while enabled; the master switch defaults OFF.
        config(['services.sso.enabled' => true, 'services.sso.shared_secret' => $this->secret]);
    }

    private function token(string $secret, string $email, ?int $expiresAt = null): string
    {
        $data = json_encode(['email' => $email, 'expires_at' => $expiresAt ?? (time() + 3600), 'timestamp' => time()]);

        return base64_encode(json_encode(['data' => $data, 'signature' => hash_hmac('sha256', $data, $secret)]));
    }

    private function user(string $status = 'Active'): User
    {
        return User::forceCreate([
            'email' => Str::lower(Str::random(8)).'@test.local',
            'password' => bcrypt('password'), 'status' => $status,
        ]);
    }

    public function test_a_token_signed_with_the_dedicated_secret_logs_in(): void
    {
        $user = $this->user();
        $this->get(route('auth.sso', ['token' => $this->token($this->secret, $user->email)]))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_token_signed_with_the_application_key_is_rejected(): void
    {
        $user = $this->user();
        $this->get(route('auth.sso', ['token' => $this->token((string) config('app.key'), $user->email)]))
            ->assertRedirect(route('login'))->assertSessionHas('error', 'Invalid token signature.');
        $this->assertGuest();
    }

    public function test_sso_is_disabled_without_a_dedicated_secret_but_login_still_exists(): void
    {
        config(['services.sso.shared_secret' => null]);
        $this->assertFalse(SsoSecret::configured());
        $this->get(route('auth.sso', ['token' => $this->token((string) config('app.key'), 'whoever@test.local')]))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Single sign-on is unavailable. Please sign in with your email and password.');
        $this->assertGuest();
        $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('login.submit'), 'password login remains');
    }

    public function test_the_app_key_cannot_be_used_as_the_sso_secret(): void
    {
        config(['services.sso.shared_secret' => config('app.key')]);
        $this->assertFalse(SsoSecret::configured());
        config(['services.sso.shared_secret' => 'short']);
        $this->assertFalse(SsoSecret::configured());
    }

    public function test_expiry_and_inactive_accounts_are_still_enforced(): void
    {
        $user = $this->user();
        $inactive = $this->user('Inactive');
        $this->get(route('auth.sso', ['token' => $this->token($this->secret, $user->email, time() - 10)]))->assertSessionHas('error', 'SSO token has expired.');
        $this->get(route('auth.sso', ['token' => $this->token($this->secret, $inactive->email)]))->assertSessionHas('error', 'User not found or inactive.');
        $this->assertGuest();
    }
    public function test_the_master_switch_off_disables_sso_even_with_a_valid_secret(): void
    {
        $user = $this->user();
        config(['services.sso.enabled' => false, 'services.sso.shared_secret' => $this->secret]);

        $this->assertFalse(SsoSecret::enabled());
        $this->assertFalse(SsoSecret::configured());
        $this->get(route('auth.sso', ['token' => $this->token($this->secret, $user->email)]))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Single sign-on is unavailable. Please sign in with your email and password.');
        $this->assertGuest();
    }
}
