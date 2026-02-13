<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\SavingGroup;
use App\Models\SavingGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InviteTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $friend;
    protected SavingGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->friend = User::factory()->create();
        $this->group = SavingGroup::factory()->create(['user_id' => $this->owner->id]);
    }

    /** --- Send Invite Tests --- **/

    public function test_owner_can_send_invite_to_user()
    {
        Sanctum::actingAs($this->owner);

        $response = $this->postJson("/api/invites/{$this->friend->id}/invite", [
            'group_id' => $this->group->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('saving_group_members', [
            'group_id' => $this->group->id,
            'user_id' => $this->friend->id,
            'status' => Status::pending->value,
        ]);
    }

    public function test_non_owner_cannot_send_invite()
    {
        $stranger = User::factory()->create();
        Sanctum::actingAs($stranger);

        $response = $this->postJson("/api/invites/{$this->friend->id}/invite", [
            'group_id' => $this->group->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_send_invite_requires_valid_group()
    {
        Sanctum::actingAs($this->owner);

        $response = $this->postJson("/api/invites/{$this->friend->id}/invite", [
            'group_id' => 999, // Non-existent group
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['group_id']);
    }

    /** --- Reply Invite Tests --- **/

    public function test_user_can_accept_invitation()
    {
        $invitation = SavingGroupMember::create([
            'group_id' => $this->group->id,
            'user_id' => $this->friend->id,
            'status' => Status::pending->value,
        ]);

        Sanctum::actingAs($this->friend);

        $response = $this->postJson("/api/invites/{$invitation->id}/reply", [
            'reply' => 'yes',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Status::closed->value, $invitation->fresh()->status);
    }

    public function test_user_can_decline_invitation()
    {
        $invitation = SavingGroupMember::create([
            'group_id' => $this->group->id,
            'user_id' => $this->friend->id,
            'status' => Status::pending->value,
        ]);

        Sanctum::actingAs($this->friend);

        $response = $this->postJson("/api/invites/{$invitation->id}/reply", [
            'reply' => 'no',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Status::active->value, $invitation->fresh()->status);
    }

    public function test_user_cannot_reply_to_someone_elses_invitation()
    {
        $otherUser = User::factory()->create();
        $invitation = SavingGroupMember::create([
            'group_id' => $this->group->id,
            'user_id' => $this->friend->id,
            'status' => Status::pending->value,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/invites/{$invitation->id}/reply", [
            'reply' => 'yes',
        ]);

        $response->assertStatus(403);
    }

    public function test_reply_requires_valid_enum_value()
    {
        $invitation = SavingGroupMember::create([
            'group_id' => $this->group->id,
            'user_id' => $this->friend->id,
            'status' => Status::pending->value,
        ]);

        Sanctum::actingAs($this->friend);

        $response = $this->postJson("/api/invites/{$invitation->id}/reply", [
            'reply' => 'maybe', // Invalid value
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reply']);
    }
}
