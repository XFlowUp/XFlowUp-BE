<?php

namespace App\Repositories\Users;

use App\Models\GithubToken;
use App\Models\Users;

class UserTokenRepository
{
    public function getUserToken(Users $user): ?GithubToken
    {
        return cache()->remember("github_token_user_{$user->id}", now()->addMinutes(30), function () use ($user) {
            return GithubToken::where('user_id', $user->id)->first();
        });
    }

    public function getUserTokenById(string $userId): ?GithubToken
    {
        return cache()->remember("github_token_user_{$userId}", now()->addMinutes(30), function () use ($userId) {
            return GithubToken::where('user_id', $userId)->first();
        });
    }
}
