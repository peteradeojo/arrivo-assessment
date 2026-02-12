<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\SavingGroup;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserGroups(Request $request)
    {
        $groups = SavingGroup::where('user_id', $request->user()->id)->get();

        return self::successResponse($groups, message: "Groups retrieved successfully.");
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $group = SavingGroup::create([
            'title' => $request->title,
            'user_id' => $request->user()->id,
        ]);

        return self::successResponse($group, 201, message: "Group created successfully.");
    }

    public function getGroup(Request $request, SavingGroup $group)
    {
        // Only owners or members of a group can view that group
        if ($group->user_id == $request->user()->id) {
            return self::successResponse($group->load(['members']));
        }

        if ($group->members->find(fn($a) => $a->user_id == $request->user()->id)) {
            return self::successResponse($group->load(['members']));
        }

        return self::errorResponse("Unauthorized", 403);
    }

    public function editGroup(Request $request, SavingGroup $group) {}

    public function addFriendToGroup(Request $request, SavingGroup $group, User $friend)
    {
        if ($group->user_id != $request->user()->id) {
            // if ($group->members->find(fn($a) => $a->user_id == $request->user()->id) == false) {
            // }
            return self::errorResponse("Unauthorized", 403);
        }
    }

    public function removeFriendFromGroup(Request $request, SavingGroup $group, User $friend) {}

    public function getUserFriends(Request $request)
    {
        $friendships = Friendship::where('user_id', $request->user()->id)->get();

        return self::successResponse($friendships->load(['friend']));
    }

    public function addFriend(Request $request)
    {
        $data = $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        try {
            $friendship = Friendship::create($data + [
                'user_id' => $request->user()->id,
            ]);

            return self::successResponse($friendship->load(['friend']), 201, "Friend added successfully.");
        } catch (\Throwable $th) {
            return self::errorResponse($th->getMessage(), 500);
        }
    }

    public function removeFriend(Request $request, User $friend)
    {
        $f = Friendship::where('friend_id', $friend->id)->where('user_id', $request->user()->id)->delete();

        return self::successResponse($f, 200, "Friend removed");
    }
}
