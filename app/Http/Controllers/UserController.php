<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Friendship;
use App\Models\SavingGroup;
use App\Models\SavingGroupMember;
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

    public function editGroup(Request $request, SavingGroup $group)
    {
        $request->validate([
            'title' => 'required|string'
        ]);

        $group->title = $request->title;
        $group->save();

        return self::successResponse($group);
    }

    public function addFriendToGroup(Request $request, SavingGroup $group, User $friend)
    {
        if ($group->user_id != $request->user()->id) {
            return self::errorResponse("Unauthorized", 403);
        }

        $add = $group->members()->create([
            'user_id' => $friend->id,
            'status' => Status::pending->value,
        ]);

        return self::successResponse($add, 201, 'Friend added to group successfully.');
    }

    public function removeFriendFromGroup(Request $request, SavingGroup $group, User $friend)
    {
        if ($group->user_id != $request->user()->id) {
            return self::errorResponse("Unauthorized", 403);
        }

        $dd = $group->members()->where('user_id', $friend->id)->delete();
        return self::successResponse(['deleted' => $dd]);
    }

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

    public function sendInvite(Request $request, User $recipient)
    {

        $request->validate([
            'group_id' => 'required|exists:saving_groups,id',
        ]);

        $group = SavingGroup::findOrFail($request->group_id);

        if ($request->user()->cannot('inviteUserToGroup', $group)) {
            abort(403);
        }

        $member = SavingGroupMember::create([
            'group_id' => $request->group_id,
            'user_id' => $recipient->id,
            'status' => Status::pending->value,
        ]);

        // TODO: Notify user of invite via email

        return self::successResponse($member, 201, "Invite sent.");
    }

    public function replyGroupInvite(Request $request, SavingGroupMember $invitation)
    {
        if ($request->user()->cannot('replyGroupInvite', $invitation)) {
            abort(403);
        }

        $request->validate([
            'reply' => 'required|string|in:yes,no',
        ]);

        $accept = $request->reply === 'yes';

        if ($accept) {
            $invitation->status = Status::closed->value;
        } else {
            $invitation->status = Status::active->value;
        }
    }
}
