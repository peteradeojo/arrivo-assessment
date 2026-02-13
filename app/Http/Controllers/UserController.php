<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Friendship;
use App\Models\SavingGroup;
use App\Models\SavingGroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/groups",
        summary: "Get all groups the user belongs to",
        security: [['bearerAuth' => []]],
        tags: ["Groups"],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function getUserGroups(Request $request)
    {
        $groups = SavingGroup::where('user_id', $request->user()->id)->get();

        return self::successResponse($groups, message: "Groups retrieved successfully.");
    }

    #[OA\Post(
        path: "/api/groups",
        summary: "Create a new group",
        security: [['bearerAuth' => []]],
        tags: ['Groups'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "December Vacation")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function createGroup(Request $request)
    {
        $request->validate([
            'title' => 'required|string'
        ]);

        $group = SavingGroup::create([
            'title' => $request->title,
            'user_id' => $request->user()->id,
        ]);
        return self::successResponse($group, 201, message: "Group created successfully.");
    }

    #[OA\Get(
        path: "/api/groups/{group}",
        summary: "Get specific group details",
        security: [['bearerAuth' => []]],
        tags: ["Groups"],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Unauthorized")
        ]
    )]
    public function getGroup(Request $request, SavingGroup $group)
    {
        if (
            $group->user_id == $request->user()->id || $group->members->find(fn($a) => $a->user_id == $request->user()->id)
        ) {
            return self::successResponse($group->load(['members']));
        }

        return self::errorResponse("Unauthorized", 403);
    }

    #[OA\Patch(
        path: "/api/groups/{group}",
        summary: "Edit a group's information",
        security: [['bearerAuth' => []]],
        tags: ['Groups'],
        parameters: [
            new OA\Parameter(name: 'group', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [new OA\Property(property: "title", type: "string")])
        ),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function editGroup(Request $request, SavingGroup $group)
    {
        $request->validate([
            'title' => 'required|string'
        ]);

        $group->title = $request->title;
        $group->save();
        return self::successResponse($group);
    }

    #[OA\Post(
        path: "/api/groups/{group}/friends/{friend}",
        summary: "Add a friend to a specific group",
        security: [['bearerAuth' => []]],
        tags: ["Groups"],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "friend", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 201, description: "Friend added")]
    )]
    public function addFriendToGroup(Request $request, SavingGroup $group, User $friend)
    {
        if ($group->user_id != $request->user()->id) return self::errorResponse("Unauthorized", 403);

        $add = $group->members()->create(['user_id' => $friend->id, 'status' => Status::pending->value]);

        return self::successResponse($add, 201, 'Friend added to group successfully.');
    }

    #[OA\Delete(
        path: "/api/groups/{group}/friends/{friend}",
        summary: "Remove a friend from a specific group",
        security: [['bearerAuth' => []]],
        tags: ["Groups"],
        parameters: [
            new OA\Parameter(name: "group", in: "path", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "friend", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 200, description: "Friend removed")]
    )]
    public function removeFriendFromGroup(Request $request, SavingGroup $group, User $friend)
    {
        if ($group->user_id != $request->user()->id) return self::errorResponse("Unauthorized", 403);

        $dd = $group->members()->where('user_id', $friend->id)->delete();

        return self::successResponse(['deleted' => $dd]);
    }

    #[OA\Get(
        path: "/api/friends",
        summary: "List user friends",
        security: [['bearerAuth' => []]],
        tags: ["Friends"],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function getUserFriends(Request $request)
    {
        $friendships = Friendship::where('user_id', $request->user()->id)->get();

        return self::successResponse($friendships->load(['friend']));
    }

    #[OA\Post(
        path: "/api/friends",
        summary: "Add a friend",
        security: [['bearerAuth' => []]],
        tags: ["Friends"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [new OA\Property(property: "friend_id", type: "integer")])
        ),
        responses: [new OA\Response(response: 201, description: "Friendship created")]
    )]
    public function addFriend(Request $request)
    {
        $data = $request->validate([
            'friend_id' => 'required|exists:users,id'
        ]);

        try {
            $friendship = Friendship::create($data + ['user_id' => $request->user()->id]);
            return self::successResponse($friendship->load(['friend']), 201, "Friend added successfully.");
        } catch (\Throwable $th) {
            return self::errorResponse($th->getMessage(), 500);
        }
    }

    #[OA\Delete(
        path: "/api/friends/{friend}",
        summary: "Remove a friend",
        security: [['bearerAuth' => []]],
        tags: ["Friends"],
        parameters: [new OA\Parameter(name: "friend", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [new OA\Response(response: 200, description: "Friend removed")]
    )]
    public function removeFriend(Request $request, User $friend)
    {
        $f = Friendship::where('friend_id', $friend->id)->where('user_id', $request->user()->id)->delete();

        return self::successResponse($f, 200, "Friend removed");
    }

    #[OA\Post(
        path: "/api/invites/{recipient}/invite",
        summary: "Send group invite to a user",
        security: [['bearerAuth' => []]],
        tags: ["Invites"],
        parameters: [new OA\Parameter(name: "recipient", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [new OA\Property(property: "group_id", type: "integer")])
        ),
        responses: [new OA\Response(response: 201, description: "Invite sent")]
    )]
    public function sendInvite(Request $request, User $recipient)
    {
        $request->validate([
            'group_id' => 'required|exists:saving_groups,id'
        ]);

        $group = SavingGroup::findOrFail($request->group_id);

        if ($request->user()->cannot('inviteUserToGroup', $group)) abort(403);

        $member = SavingGroupMember::create([
            'group_id' => $request->group_id,
            'user_id' => $recipient->id,
            'status' => Status::pending->value,
        ]);

        return self::successResponse($member, 201, "Invite sent.");
    }

    #[OA\Post(
        path: "/api/invites/{invitation}/reply",
        summary: "Reply to a group invitation",
        security: [['bearerAuth' => []]],
        tags: ["Invites"],
        parameters: [new OA\Parameter(name: "invitation", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "reply", type: "string", enum: ["yes", "no"])
            ])
        ),
        responses: [new OA\Response(response: 200, description: "Reply processed")]
    )]
    public function replyGroupInvite(Request $request, SavingGroupMember $invitation)
    {
        $request->validate([
            'reply' => 'required|string|in:yes,no'
        ]);

        if ($request->user()->id !== $invitation->user_id) abort(403);

        $accept = $request->reply === 'yes';
        $invitation->status = $accept ? Status::closed->value : Status::active->value;
        $invitation->save(); // Don't forget to save!

        return self::successResponse([], message: "Response recorded.");
    }
}
