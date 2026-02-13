<?php

namespace Tests\Unit;

use App\Models\SavingGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_user_can_register(): void
    {
        $response = $this->withHeaders([
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ])->postJson('/api/register', [
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'name' => 'Test'
        ]);

        $response->assertJson(fn(AssertableJson $res) => $res->has('message')->etc());
        $response->assertCreated();
    }

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $res) =>
            $res->has('data')
                ->where(
                    'data',
                    fn(Collection $data) =>
                    $data->has('token'),
                )
                ->etc()
        );
    }

    public function test_user_can_add_friend()
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/friends', [
            'friend_id' => $friend->id,
        ]);

        $response->assertCreated();

        $response = $this->actingAs($user)->get('/api/friends');

        $response->assertJson(
            fn(AssertableJson $res) =>
            $res
                ->where(
                    'data',
                    fn(Collection $data) =>
                    $data->where('friend_id', $friend->id)->first() != null
                )
                ->etc()
        );
    }

    public function test_user_can_create_saving_plan()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/saving-plans', [
            'description' => 'Lorem ipsum dolor sit amet',
        ]);

        $response->assertCreated();
    }

    public function test_user_can_create_saving_plan_with_target()
    {
        $user = User::factory()->create();

        $target = 25000;
        $response = $this->actingAs($user)->postJson('/api/saving-plans', [
            'description' => 'Lorem ipsum dolor sit amet',
            'target' => $target
        ]);

        $response->assertJson(
            fn (AssertableJson $res) =>
                $res
                ->has('data.id')
                ->where('data.target', $target)
                ->etc(),
        );
    }


    public function test_user_can_create_saving_plan_in_group()
    {
        $user = User::factory()->create();
        $group = SavingGroup::create([
            'user_id' => $user->id,
            'title' => 'Lorem',
        ]);

        $response = $this->actingAs($user)->postJson('/api/saving-plans', [
            'description' => 'Lorem ipsum dolor sit amet',
            'group_id' => $group->id,
        ]);

        $response->assertCreated();

        $response->dump();

        $response->assertJson(
            fn (AssertableJson $res) =>
                $res->has('data.id')
                ->where('data.owner.id', $group->id)
                ->where('data.owner.title', $group->title)
                ->etc()
        );
    }
}
