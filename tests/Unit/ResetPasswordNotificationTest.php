<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotificationTest extends TestCase
{
    /** @test */
    public function reset_password_notification_contains_valid_reset_link()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'test-token-123';

        $notification = new ResetPasswordNotification($token);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString($token, $mail->actionUrl);
        $this->assertStringContainsString('test@example.com', $mail->actionUrl);
        $this->assertStringContainsString('reset-password', $mail->actionUrl);
    }

    /** @test */
    public function reset_password_notification_has_correct_subject()
    {
        $notification = new ResetPasswordNotification('test-token');
        $mail = $notification->toMail(User::factory()->make());

        $this->assertEquals('Reset Password Request', $mail->subject);
    }

    /** @test */
    public function reset_password_notification_contains_expected_content()
    {
        $notification = new ResetPasswordNotification('test-token');
        $mail = $notification->toMail(User::factory()->make());

        $this->assertStringContainsString('Click the button below to reset your password.', $mail->introLines[0]);
        $this->assertStringContainsString('Reset Password', $mail->actionText);
        $this->assertStringContainsString('If you did not request this, no further action is required.', $mail->outroLines[0]);
    }

    /** @test */
    public function reset_password_notification_uses_correct_frontend_url()
    {
        config(['app.frontend_url' => 'http://localhost:3000']);

        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'test-token-456';

        $notification = new ResetPasswordNotification($token);
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('http://localhost:3000/reset-password', $mail->actionUrl);
    }
}
