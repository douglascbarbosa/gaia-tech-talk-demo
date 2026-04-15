<?php

namespace Tests\Feature\Auth;

use App\Models\Developer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * Feature tests for login, logout, two-factor redirect, and login rate limiting.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The login page renders successfully for guests.
     */
    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    /**
     * Valid credentials authenticate and redirect to the dashboard.
     */
    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = Developer::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Developers with 2FA enabled are redirected to the two-factor challenge after password check.
     */
    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = Developer::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('login.id', $user->id);
        $this->assertGuest();
    }

    /**
     * Wrong password keeps the session unauthenticated.
     */
    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = Developer::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /**
     * POST logout clears authentication and redirects home.
     */
    public function test_users_can_logout()
    {
        $user = Developer::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    /**
     * Too many failed attempts trigger the rate limiter response.
     */
    public function test_users_are_rate_limited()
    {
        $user = Developer::factory()->create();

        RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }
}
