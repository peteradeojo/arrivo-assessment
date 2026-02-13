<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('/saving-plans')->group(function () {
        Route::get('/', [SavingsController::class, 'getUserSavingPlans']);
        Route::post('/', [SavingsController::class, 'store']);
        Route::post('/{plan}/fund', [SavingsController::class, 'fundSavingPlan']);
        Route::patch('/:plan/edit', [SavingsController::class, 'editPlan']);
    });

    // GROUPS
    Route::get('/groups', [UserController::class, 'getUserGroups']);
    Route::post('/groups', [UserController::class, 'createGroup']);
    Route::get('/groups/{group}', [UserController::class, 'getGroup']);
    Route::patch('/groups/{group}', [UserController::class, 'editGroup']);
    Route::post('/groups/{group}/friends/{friend}', [UserController::class, 'addFriendToGroup']);
    Route::delete('/groups/{group}/friends/{friend}', [UserController::class, 'removeFriendFromGroup']);

    // FRIENDS
    Route::get('/friends', [UserController::class, 'getUserFriends']);
    Route::post('/friends', [UserController::class, 'addFriend']);
    Route::delete('/friends/{friend}', [UserController::class, 'removeFriend']);

    // INVITESin
    Route::prefix('/invites')->group(function () {
        Route::get('/', [UserController::class, 'getInvites']);
        Route::post('/{group}/invite', [UserController::class, 'sendGroupInvite']);
        Route::post('/{invitation}/reply', [UserController::class, 'replyGroupInvite']);
    });
});

Route::middleware(['role:admin'])->group(function () {});
