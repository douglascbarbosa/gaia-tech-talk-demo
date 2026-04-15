<?php

namespace Tests\Feature\Auth;

use App\Models\Developer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Feature tests for the password confirmation gate page.
 */
class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticated developers can load the confirm-password Inertia page.
     */
    public function test_confirm_password_screen_can_be_rendered()
    {
        $user = Developer::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('auth/ConfirmPassword'),
        );
    }

    /**
     * Guests are redirected to login when hitting the confirm-password route.
     */
    public function test_password_confirmation_requires_authentication()
    {
        $response = $this->get(route('password.confirm'));

        $response->assertRedirect(route('login'));
    }
}
