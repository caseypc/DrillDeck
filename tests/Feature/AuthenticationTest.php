<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_owner_registration_creates_board(): void
    {
        $this->post('/register', [
            'name'=>'Owner',
            'email'=>'owner@example.test',
            'password'=>'a-very-long-password',
            'password_confirmation'=>'a-very-long-password',
            'shop_name'=>'Test Bowling',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $shop = Shop::firstOrFail();
        $this->assertSame(6, $shop->columns()->count());
        $this->assertSame('owner', $shop->employees()->first()->pivot->role);
    }

    public function test_customer_registration_does_not_create_shop(): void
    {
        $this->post('/register', [
            'name'=>'Customer',
            'email'=>'customer@example.test',
            'password'=>'a-very-long-password',
            'password_confirmation'=>'a-very-long-password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseCount('shops', 0);
    }

    public function test_login_and_logout(): void
    {
        $user = User::create([
            'name'=>'Employee',
            'email'=>'employee@example.test',
            'password'=>Hash::make('a-very-long-password'),
        ]);

        $this->post('/login', [
            'email'=>$user->email,
            'password'=>'a-very-long-password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_password_reset_request_sends_notification(): void
    {
        Notification::fake();
        $user = User::create([
            'name'=>'Customer',
            'email'=>'reset@example.test',
            'password'=>Hash::make('a-very-long-password'),
        ]);

        $this->post('/forgot-password',['email'=>$user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_changes_password(): void
    {
        $user = User::create([
            'name'=>'Reset Customer',
            'email'=>'reset2@example.test',
            'password'=>Hash::make('original-long-password'),
        ]);

        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token'=>$token,
            'email'=>$user->email,
            'password'=>'new-long-password-123',
            'password_confirmation'=>'new-long-password-123',
        ])->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-long-password-123', $user->fresh()->password));
    }

    public function test_guest_cannot_access_shop(): void
    {
        $this->get('/shop')->assertRedirect('/login');
    }
}
