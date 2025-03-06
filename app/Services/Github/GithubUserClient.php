<?php

namespace App\Services\Github;

use Github\Client as GitHubClient;
use Illuminate\Support\Facades\Auth;

class GithubUserClient
{
    static function getClientForUser(): GitHubClient
    {
        $user = Auth::user();
        $githubToken = $user->githubToken()->first();
        $client = new GitHubClient();
        $client->authenticate($githubToken->access_token, null, GitHubClient::AUTH_ACCESS_TOKEN);
        return $client;
    }
}
