<?php

namespace App\Http\Controllers;

use App\Models\SavingGroup;
use App\Models\SavingPlan;
use App\Models\User;
use Illuminate\Http\Request;

class SavingsController extends Controller
{
    public function getUserSavingPlans(Request $request)
    {
        $user = $request->user();
        $plans = SavingPlan::with(['owner'])->where('user_id', $user->id)->get();

        return self::successResponse($plans, message: "Plans retrieved.");
    }

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
            // $plan->owner()->attach($group);
        } else {
            $plan->owner_type = User::class;
            $plan->owner_id = $request->user()->id;
        }

        $plan->save();

        return self::successResponse($plan, 200, "Plan created successfully!");
    }
}
