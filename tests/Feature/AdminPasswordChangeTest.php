<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordChangeTest extends TestCase
{
    public function test_admin_can_open_password_change_form(): void
    {
        $admin = User::create([
            'name' => 'Password Form Admin',
            'email' => 'password-form-admin@example.test',
            'password' => 'CurrentPassword123!',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.account.password'));

        $response->assertOk();
        $response->assertSee('Current password');
        $response->assertSee('Confirm new password');
        $response->assertSee('Cancel');
    }

    public function test_admin_can_change_password_after_entering_the_current_password(): void
    {
        $admin = User::create([
            'name' => 'Password Test Admin',
            'email' => 'password-test-admin@example.test',
            'password' => 'CurrentPassword123!',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.account.password.update'), [
            'current_password' => 'CurrentPassword123!',
            'password' => 'FreshPassword456ABC!',
            'password_confirmation' => 'FreshPassword456ABC!',
        ]);

        $response->assertRedirect(route('admin.account.password'));
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('FreshPassword456ABC!', $admin->fresh()->password));
    }

    public function test_admin_cannot_change_password_with_an_incorrect_current_password(): void
    {
        $admin = User::create([
            'name' => 'Password Test Admin',
            'email' => 'password-test-admin@example.test',
            'password' => 'CurrentPassword123!',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.account.password.update'), [
            'current_password' => 'IncorrectPassword123!',
            'password' => 'FreshPassword456ABC!',
            'password_confirmation' => 'FreshPassword456ABC!',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('CurrentPassword123!', $admin->fresh()->password));
    }

    public function test_guests_cannot_access_password_change_form(): void
    {
        $this->get(route('admin.account.password'))->assertRedirect(route('login'));
    }
}