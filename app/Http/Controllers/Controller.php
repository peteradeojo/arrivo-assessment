<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public static function errorResponse(string $errorMessage, int $status = 500)
    {
        return response()->json([
            'message' => $errorMessage,
            'data' => null,
        ], $status);
    }

    public static function successResponse(mixed $data, int $status = 200, string $message = '')
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
