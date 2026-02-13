<?php

namespace App\Http\Controllers;

use App\Models\SavingGroup;
use App\Models\SavingPlan;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SavingsController extends Controller
{
    #[OA\Get(
        path: "/api/saving-plans",
        summary: "List user saving plans",
        security: [['bearerAuth' => []]],
        tags: ["Savings"],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function getUserSavingPlans(Request $request)
    {
        $user = $request->user();
        $plans = SavingPlan::with(['owner'])->where('user_id', $user->id)->get();

        return self::successResponse($plans, message: "Plans retrieved.");
    }

    #[OA\Post(
        path: "/api/saving-plans",
        summary: "Create a new saving plan",
        description: "Creates a personal or group-based saving plan. If group_id is provided, the plan is owned by the group.",
        security: [['bearerAuth' => []]],
        tags: ["Savings"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["description"],
                properties: [
                    new OA\Property(property: "description", type: "string", example: "Save for New Laptop"),
                    new OA\Property(property: "target", type: "number", format: "float", nullable: true, example: 1500.00),
                    new OA\Property(property: "expire_date", type: "string", format: "date", nullable: true, example: "2026-12-31"),
                    new OA\Property(property: "group_id", type: "integer", nullable: true, example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Plan created successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'target' => 'numeric|nullable|bail',
            'expire_date' => 'date|nullable|after:today',
            'group_id' => 'nullable|exists:saving_groups,id'
        ]);

        $plan = new SavingPlan([
            'user_id' => $request->user()->id,
            ...$request->only(['description', 'target', 'expire_date']),
        ]);

        if ($request->has('group_id')) {
            $group = SavingGroup::findOrFail($request->group_id);
            $plan->owner_type = $group::class;
            $plan->owner_id = $group->id;
        } else {
            $plan->owner_type = User::class;
            $plan->owner_id = $request->user()->id;
        }

        $plan->save();
        $plan->refresh()->load('owner');

        return self::successResponse($plan, 201, "Plan created successfully!");
    }
}
