<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string', // for mobile device requests
        ]);

        /**
         * @var User
         */
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken($request->device_name ?? 'auth')->plainTextToken;

            return self::successResponse([
                'token' => $token,
                'user' => $user,
            ], 200);
        }

        return self::errorResponse("Invalid credentials!", 400);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
            'name' => 'required|string',
        ]);

        $user = User::create([
            ...$request->except('password_confirmation'),
            'email_verified_at' => now(), // ! IN PLACE OF E-MAIL VERIFICATION
        ]);

        // TODO: send a verification email so the user can complete the verification flow

        return self::successResponse([], 201, "Sign-up successful. Please login");
    }
}
