<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * @OA\Get(
 *     path="/api/user/info",
 *     summary="Get user information",
 *     tags={"User"},
 *     @OA\Parameter(
 *         name="Authorization",
 *         in="header",
 *         required=true,
 *         @OA\Schema(type="string", example="Bearer {token}")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User information retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object", ref="#/components/schemas/User")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="User not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="User not found")
 *         )
 *     )
 * )
 * @OA\SecurityScheme(
 *     securityScheme="Bearer",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class InfoController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function getUserInfo()
    {
        $user = $this->userService->getUserInfo();
        if (!$user) {
            throw new HttpResponseException(response()->json([
                "success" => false,
                "message" => "Unauthorized"
            ], 401));
        }
        return response()->json([
            "success" => true,
            "data" => $user
        ]);
    }
}
