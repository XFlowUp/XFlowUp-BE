<?php

namespace App\Services\Auth;

use Laravel\Socialite\Contracts\User as SocialiteUser;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthService
{
    public function saveUser(SocialiteUser $githubUser): array
    {
        try {
            $user = Users::updateOrCreate(
                [
                    "email" => $githubUser->getEmail(),
                ],
                [
                    "profile_pic_url" => $githubUser->getAvatar(),
                    "plan_id" => 1,
                    "github_id" => $githubUser->getId(),
                ]
            );

            $userToken = $user->githubToken()->first();

            if ($userToken) {
                $userToken->update([
                    'access_token' => $githubUser->token,
                    'refresh_token' => $githubUser->refreshToken ?? $githubUser->token,
                ]);
            } else {
                $user->githubToken()->create([
                    'access_token' => $githubUser->token,
                    'refresh_token' => $githubUser->refreshToken ?? $githubUser->token,
                ]);
            }

            $token = Auth::guard('api')->login($user);
            $ttl = auth('api')->factory()->getTTL() * 60;

            return [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
