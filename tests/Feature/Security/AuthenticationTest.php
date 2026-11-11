<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_inactive_user_is_logged_out(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_user_without_role_cannot_access_dashboard(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertForbidden();
    }
}
