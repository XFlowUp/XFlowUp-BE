<?php

namespace App\Services\User;

use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserService
{
    public function getUserInfo(): ?Users
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        return Cache::remember(
            "user_info_{$user->id}",
            now()->addMinutes(config('cache-settings.user_info_duration', 120)),
            function () use ($user) {
                return Users::find($user->id);
            }
        );
    }
}
