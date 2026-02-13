<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

// define('L5_SWAGGER_CONST_HOST', config('l5-swagger.defaults.constants.L5_SWAGGER_CONST_HOST'));

#[OA\Info(
    version: "1.0.0",
    title: "Arrivo API Documentation",
    description: "API endpoints for Savings, Groups, and User Management",
    contact: new OA\Contact(email: "support@example.com"),
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Primary API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
)]
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
