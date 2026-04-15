<?php

namespace Tests\Feature\Settings;

use App\Models\Developer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the authenticated developer profile settings routes.
 */
class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The profile edit page loads for an authenticated developer.
     */
    public function test_profile_page_is_displayed()
    {
        $user = Developer::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    /**
     * Core profile fields can be updated; changing email clears verification timestamp.
     */
    public function test_profile_information_can_be_updated()
    {
        $user = Developer::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'status' => 'working',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    /**
     * When email is unchanged, the verified-at timestamp remains set.
     */
    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = Developer::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
                'status' => 'working',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    /**
     * GitHub profile URL and availability status persist through the profile update endpoint.
     */
    public function test_developer_can_update_github_profile_and_availability_status(): void
    {
        $user = Developer::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'github_profile' => 'https://github.com/example',
                'status' => 'open_for_new_jobs',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('https://github.com/example', $user->github_profile);
        $this->assertSame('open_for_new_jobs', $user->status);
    }

    /**
     * A developer can delete their account with the correct password.
     */
    public function test_user_can_delete_their_account()
    {
        $user = Developer::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    /**
     * Account deletion fails validation when the password is wrong.
     */
    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $user = Developer::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
    }
}
