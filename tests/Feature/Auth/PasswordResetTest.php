<?php

namespace Tests\Feature\Auth;

use App\Models\Developer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * Feature tests for Fortify password reset request and completion.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skip tests when password reset is not enabled.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::resetPasswords());
    }

    /**
     * The forgot-password request page loads.
     */
    public function test_reset_password_link_screen_can_be_rendered()
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    /**
     * Requesting a reset link queues the ResetPassword notification to the developer.
     */
    public function test_reset_password_link_can_be_requested()
    {
        Notification::fake();

        $user = Developer::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * The reset form can be opened using the token from the notification.
     */
    public function test_reset_password_screen_can_be_rendered()
    {
        Notification::fake();

        $user = Developer::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get(route('password.reset', $notification->token));

            $response->assertOk();

            return true;
        });
    }

    /**
     * Submitting a valid token and new password updates credentials and redirects to login.
     */
    public function test_password_can_be_reset_with_valid_token()
    {
        Notification::fake();

        $user = Developer::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    /**
     * An invalid token yields validation errors and does not reset the password.
     */
    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = Developer::factory()->create();

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
