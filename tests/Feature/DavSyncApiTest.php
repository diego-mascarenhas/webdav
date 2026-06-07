<?php

namespace Tests\Feature;

use App\Dav\Services\DavUserProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DavSyncApiTest extends TestCase
{
    use RefreshDatabase;

    private const API_TOKEN = 'test-api-token';

    private function createProvisionedUser(): array
    {
        $provisioner = app(DavUserProvisioner::class);

        return $provisioner->create(
            email: 'sync@example.com',
            name: 'Sync User',
            davUsername: 'syncuser',
            password: 'sync-password',
        );
    }

    public function test_can_list_tasks_after_provisioning(): void
    {
        $this->createProvisionedUser();

        $response = $this->withToken(self::API_TOKEN)->getJson('/api/tasks?email=sync@example.com');

        $response->assertOk()
            ->assertJsonPath('meta.count', 0);
    }

    public function test_can_create_and_delete_contact_via_api(): void
    {
        $this->createProvisionedUser();

        $create = $this->withToken(self::API_TOKEN)->postJson('/api/contacts?email=sync@example.com', [
            'name' => 'Ana',
            'surname' => 'García',
            'email' => 'ana@example.com',
            'uid' => 'contact-uid-1',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.uid', 'contact-uid-1');

        $list = $this->withToken(self::API_TOKEN)->getJson('/api/contacts?email=sync@example.com');
        $list->assertOk()->assertJsonPath('meta.count', 1);

        $delete = $this->withToken(self::API_TOKEN)->deleteJson('/api/contacts/contact-uid-1?email=sync@example.com');
        $delete->assertOk();
    }

    public function test_can_create_task_via_api(): void
    {
        $this->createProvisionedUser();

        $response = $this->withToken(self::API_TOKEN)->postJson('/api/tasks?email=sync@example.com', [
            'summary' => 'Buy milk',
            'due_at' => '2026-06-10T10:00:00+00:00',
            'uid' => 'task-uid-1',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.uid', 'task-uid-1');

        $list = $this->withToken(self::API_TOKEN)->getJson('/api/tasks?email=sync@example.com');
        $list->assertOk()->assertJsonPath('meta.count', 1);
    }
}
