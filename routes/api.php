<?php

use App\Http\Controllers\AdminController;
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
    Route::prefix('/saving-plans')->middleware(['throttle:20,1'])->group(function () {
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
    Route::get('/friends', [UserController::class, 'getUserFriends'])->middleware(['throttle:10,1']);
    Route::post('/friends', [UserController::class, 'addFriend']);
    Route::delete('/friends/{friend}', [UserController::class, 'removeFriend']);

    // INVITES
    Route::prefix('/invites')->group(function () {
        Route::get('/', [UserController::class, 'getInvites'])->middleware(['throttle:30,1']);
        Route::post('/{recipient}/invite', [UserController::class, 'sendInvite']);
        Route::post('/{invitation}/reply', [UserController::class, 'replyGroupInvite']);
    });
});

Route::middleware(['auth:sanctum', 'role:superadmin', 'throttle:60,1'])->prefix('/admin')->group(function () {
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/users/{user}', [AdminController::class, 'showUser']);

    Route::get('/groups', [AdminController::class, 'viewAllGroups']);
    Route::get('/groups/{group}', [AdminController::class, 'viewGroup']);

    Route::get('/saving-plans', [AdminController::class, 'viewSavingPlans']);
    Route::get('/saving-plans/{plan}', [AdminController::class, 'viewSavingPlan']);

    // TODO
    // Route::get('/transactions', [AdminController::class, 'viewTransactions']);
    // Route::get('/transactions/{txn}', [AdminController::class, 'viewTransactionDetails']);
});
