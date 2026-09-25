<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/account/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/account/profile');

        $this->assertTrue(Hash::check('NewPassword123', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/account/profile')
            ->put('/password', [
                'current_password' => 'wrong-password',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect('/account/profile');
    }

    public function test_updated_password_must_include_mixed_case_letters_and_a_number(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/account/profile')
            ->put('/password', [
                'current_password' => 'password',
                'password' => 'alllowercase',
                'password_confirmation' => 'alllowercase',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePassword', 'password')
            ->assertRedirect('/account/profile');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
