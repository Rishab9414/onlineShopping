<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_available_at_both_urls(): void
    {
        $this->get('/admin')->assertOk();
        $this->get('/admin/login')->assertOk()->assertSee('Sign In to Dashboard');
    }

    public function test_admin_can_sign_in_from_login_form(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@ridhisidhi.test',
            'password' => 'Admin@12345',
            'is_admin' => true,
            'status' => true,
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'Admin@12345',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }
}
