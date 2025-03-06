<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;


/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints for user authentication"
 * )
 */
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Get(
     *     path="/api/auth/github",
     *     operationId="redirectToGithub",
     *     tags={"Authentication"},
     *     summary="Redirect to GitHub for OAuth",
     *     description="Redirects the user to GitHub OAuth authorization page",
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to GitHub"
     *     )
     * )
     */
    public function redirectToGithub(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes([
                'repo',              // Full control of private repositories
                'admin:repo_hook',   // Full control of repository webhooks
                'write:repo_hook',   // Write repository hooks (create/edit)
                'read:repo_hook',    // Read repository hooks
                'admin:org_hook',    // Read and write org hooks
                'workflow',          // Update GitHub Action workflows
                'read:org',          // Read org and team membership, read org projects
                'read:public_key',   // Read public keys
                'read:user',         // Read user profile information
                'user:email',        // Access user email addresses (read-only)
            ])
            ->redirect();
    }

    /**
     * @OA\Get(
     *     path="/api/auth/github/callback",
     *     operationId="handleGithubCallback",
     *     tags={"Authentication"},
     *     summary="Handle GitHub OAuth callback",
     *     description="Process the GitHub OAuth callback and authenticate user",
     *     @OA\Response(
     *         response=200,
     *         description="Successfully authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User saved successfully"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Authentication failed"
     *     )
     * )
     */
    public function handleGithubCallback(): JsonResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();
            $user = $this->authService->saveUser($githubUser);
            return response()->json([
                'message' => 'User saved successfully',
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to save user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     operationId="getUserInfo",
     *     tags={"Authentication"},
     *     summary="Get authenticated user info",
     *     description="Returns information about the currently authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Not authenticated"
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json([
            'user' => Auth::user()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     operationId="logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     description="Logs out the currently authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Not authenticated"
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
