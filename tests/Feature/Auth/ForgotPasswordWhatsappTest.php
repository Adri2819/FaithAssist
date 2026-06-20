<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordResetWhatsappCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ForgotPasswordWhatsappTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_a_whatsapp_code_for_valid_user(): void
    {
        config()->set('services.whatsapp.enabled', true);
        config()->set('services.whatsapp.token', 'test-token');
        config()->set('services.whatsapp.phone_number_id', '1234567890');

        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200),
        ]);

        $user = User::query()->create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario@faithassistqr.test',
            'whatsapp_phone' => '+5215512345678',
            'password' => 'password123',
        ]);

        $this->post(route('password.forgot.send-code'), [
            'whatsapp_country_code' => '521',
            'whatsapp_phone' => '5512345678',
        ])->assertRedirect(route('password.forgot.form'));

        $this->assertDatabaseHas('password_reset_whatsapp_codes', [
            'user_id' => $user->id,
        ]);

        Http::assertSent(function ($request) use ($user) {
            return str_contains($request->url(), '/messages')
                && $request['to'] === $user->whatsapp_phone;
        });
    }

    public function test_it_resets_password_with_valid_code(): void
    {
        $user = User::query()->create([
            'name' => 'Usuario Prueba',
            'email' => 'usuario2@faithassistqr.test',
            'whatsapp_phone' => '+5215598765432',
            'password' => 'old-password',
        ]);

        PasswordResetWhatsappCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('password.forgot.reset'), [
            'whatsapp_country_code' => '521',
            'whatsapp_phone' => '5598765432',
            'code' => '123456',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_whatsapp_codes', [
            'user_id' => $user->id,
        ]);
    }
}
