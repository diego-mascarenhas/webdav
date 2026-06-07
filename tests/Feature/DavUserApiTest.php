<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DavUserApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_TOKEN = 'test-api-token';

    public function test_user_api_requires_valid_token(): void
    {
        $response = $this->postJson('/api/users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_api_does_not_require_csrf_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.self::API_TOKEN,
        ])->post('/api/users', [
            'email' => 'csrf-free@example.com',
            'name' => 'CSRF Free',
            'password' => 'secret-password',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'csrf-free@example.com');
    }

    public function test_can_create_dav_user_via_api(): void
    {
        $response = $this->withToken(self::API_TOKEN)->postJson('/api/users', [
            'email' => 'new@example.com',
            'name' => 'New User',
            'dav_username' => 'newuser',
            'password' => 'secret-password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonPath('data.dav_username', 'newuser')
            ->assertJsonPath('data.principal', 'principals/newuser')
            ->assertJsonPath('meta.created', true);

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'dav_username' => 'newuser',
        ]);

        $this->assertDatabaseHas('principals', [
            'uri' => 'principals/newuser',
        ]);

        $this->assertDatabaseHas('addressbooks', [
            'uri' => 'default',
        ]);
    }

    public function test_can_link_existing_dav_user_via_api(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'dav_username' => 'existing',
            'password' => 'link-password',
        ]);

        $response = $this->withToken(self::API_TOKEN)->postJson('/api/users/link', [
            'email' => 'existing@example.com',
            'password' => 'link-password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'existing@example.com')
            ->assertJsonPath('meta.linked', true);
    }

    public function test_link_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
            'password' => 'correct-password',
        ]);

        $response = $this->withToken(self::API_TOKEN)->postJson('/api/users/link', [
            'email' => 'existing@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_can_show_user_by_email(): void
    {
        User::factory()->create([
            'email' => 'show@example.com',
            'dav_username' => 'showuser',
        ]);

        $response = $this->withToken(self::API_TOKEN)->getJson('/api/users?email=show@example.com');

        $response->assertOk()
            ->assertJsonPath('data.email', 'show@example.com')
            ->assertJsonPath('data.principal', 'principals/showuser');
    }

    public function test_can_update_user_password(): void
    {
        $user = User::factory()->create([
            'email' => 'pass@example.com',
            'password' => 'old-password',
        ]);

        $response = $this->withToken(self::API_TOKEN)->putJson('/api/users/password', [
            'email' => 'pass@example.com',
            'password' => 'new-password-123',
        ]);

        $response->assertOk()
            ->assertJsonPath('meta.password_updated', true);

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password-123', $user->password));
    }
}
