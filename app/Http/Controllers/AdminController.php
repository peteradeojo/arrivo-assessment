<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SavingGroup;
use App\Models\SavingPlan;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    #[OA\Get(
        path: "/api/admin/users",
        summary: "Admin: View all registered users",
        security: [['bearerAuth' => []]],
        tags: ["Admin"],
        responses: [
            new OA\Response(response: 200, description: "List of all users")
        ]
    )]
    public function getUsers()
    {
        $users = User::paginate(20);
        return self::successResponse($users, message: "Users retrieved.");
    }

    #[OA\Get(
        path: "/api/admin/users/{user}",
        summary: "Admin: View details of a specific user",
        security: [['bearerAuth' => []]],
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(name: "user", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User details"),
            new OA\Response(response: 404, description: "User not found")
        ]
    )]
    public function showUser(User $user)
    {
        return self::successResponse($user->loadCount(['user_groups', 'member_groups', 'friends']), message: "User details retrieved.");
    }

    #[OA\Get(
        path: "/api/admin/groups",
        summary: "Admin: View all saving groups",
        security: [['bearerAuth' => []]],
        tags: ["Admin"],
        responses: [
            new OA\Response(response: 200, description: "List of all groups")
        ]
    )]
    public function viewAllGroups()
    {
        $groups = SavingGroup::with('user')->get();
        return self::successResponse($groups);
    }

    #[OA\Get(
        path: "/api/admin/groups/{group}",
        summary: "Admin: View specific group details",
        security: [['bearerAuth' => []]],
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Group details")
        ]
    )]
    public function viewGroup(SavingGroup $group)
    {
        return self::successResponse($group->load(['user', 'members.user']));
    }

    #[OA\Get(
        path: "/api/admin/saving-plans",
        summary: "Admin: View all saving plans",
        security: [['bearerAuth' => []]],
        tags: ["Admin"],
        responses: [
            new OA\Response(response: 200, description: "List of all plans")
        ]
    )]
    public function viewSavingPlans()
    {
        $plans = SavingPlan::with('owner')->paginate(20);
        return self::successResponse($plans);
    }

    #[OA\Get(
        path: "/api/admin/saving-plans/{plan}",
        summary: "Admin: View specific saving plan details",
        security: [['bearerAuth' => []]],
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(name: "plan", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Plan details")
        ]
    )]
    public function viewSavingPlan(SavingPlan $plan)
    {
        return self::successResponse($plan->load('owner'));
    }
}
