<?php

namespace Tests\Feature\Auth;

use App\Models\Developer;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * Feature tests for resending the email verification notification.
 */
class VerificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skip tests when email verification is not enabled.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::emailVerification());
    }

    /**
     * Unverified developers trigger a VerifyEmail notification when requesting a new link.
     */
    public function test_sends_verification_notification(): void
    {
        Notification::fake();

        $user = Developer::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect(route('home'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * Verified developers do not receive another verification notification.
     */
    public function test_does_not_send_verification_notification_if_email_is_verified(): void
    {
        Notification::fake();

        $user = Developer::factory()->create();

        $this->actingAs($user)
            ->post(route('verification.send'))
            ->assertRedirect(route('dashboard', absolute: false));

        Notification::assertNothingSent();
    }
}
