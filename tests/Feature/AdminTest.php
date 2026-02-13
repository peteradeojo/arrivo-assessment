<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SavingGroup;
use App\Models\SavingPlan;
use Database\Seeders\RoleAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        (new RoleAndPermissionsSeeder)->run();

        // Create an admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('superadmin');

        // Create a regular user
        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('user');
    }

    public function test_an_admin_can_list_all_users()
    {
        User::factory()->count(5)->create();

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'data' => ['data', 'current_page']]);
    }

    public function test_a_regular_user_cannot_access_admin_routes()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_an_admin_can_view_a_specific_user()
    {
        $targetUser = User::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/admin/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $targetUser->id);
    }

    public function test_an_admin_can_view_all_saving_groups()
    {
        SavingGroup::factory()->count(3)->create(['user_id' => $this->regularUser->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/groups');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }


    public function test_an_admin_can_view_a_specific_group()
    {
        $group = SavingGroup::factory()->create(['user_id' => $this->regularUser->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/admin/groups/{$group->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', $group->title);
    }


    public function test_an_admin_can_view_all_saving_plans()
    {
        SavingPlan::factory()->count(2)->create([
            'user_id' => $this->regularUser->id,
            'owner_id' => $this->regularUser->id,
            'owner_type' => User::class
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/saving-plans');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_an_admin_can_view_a_specific_saving_plan()
    {
        $plan = SavingPlan::factory()->create([
            'user_id' => $this->regularUser->id,
            'owner_id' => $this->regularUser->id,
            'owner_type' => User::class
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/admin/saving-plans/{$plan->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.description', $plan->description);
    }
}
