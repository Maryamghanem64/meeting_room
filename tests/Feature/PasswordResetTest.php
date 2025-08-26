<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;

class PasswordResetTest extends TestCase
{
    /** @test */
    public function forgot_password_returns_success_for_valid_email()
    {
        $user = User::factory()->create([
            'email' => 'test1@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test1@example.com'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Reset link sent!']);
    }

    /** @test */
    public function forgot_password_returns_error_for_invalid_email()
    {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function forgot_password_returns_error_for_nonexistent_email()
    {
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Unable to send reset link']);
    }

    /** @test */
    public function reset_password_notification_is_sent()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test2@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $this->postJson('/api/forgot-password', [
            'email' => 'test2@example.com'
        ]);

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class
        );
    }

    /** @test */
    public function reset_password_succeeds_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test3@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test3@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password reset successful!']);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function reset_password_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test4@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test4@example.com',
            'token' => 'invalid-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Password reset failed.']);
    }

    /** @test */
    public function reset_password_fails_with_password_mismatch()
    {
        $user = User::factory()->create([
            'email' => 'test5@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test5@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function reset_password_fails_with_weak_password()
    {
        $user = User::factory()->create([
            'email' => 'test6@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test6@example.com',
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function reset_password_token_is_deleted_after_successful_reset()
    {
        $user = User::factory()->create([
            'email' => 'test7@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $token = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'email' => 'test7@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $this->assertFalse(Password::tokenExists($user, $token));
    }

    /** @test */
    public function reset_password_fails_with_missing_required_fields()
    {
        $user = User::factory()->create([
            'email' => 'test8@example.com',
            'password' => Hash::make('oldpassword123')
        ]);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test8@example.com',
            // Missing token, password, password_confirmation
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['token', 'password']);
    }
}
