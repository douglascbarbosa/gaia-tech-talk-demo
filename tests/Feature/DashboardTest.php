<?php

namespace Tests\Feature;

use App\Models\Developer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the main dashboard route and access control.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guests hitting the dashboard are sent to the login page.
     */
    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Authenticated developers receive a successful dashboard response.
     */
    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = Developer::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }
}
