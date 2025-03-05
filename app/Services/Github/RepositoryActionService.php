<?php

namespace App\Services\Github;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Github\Client as GitHubClient;
use App\Models\Users;
use Exception;

class RepositoryActionService
{
    /**
     * Pull code from a specified repository and store it in the local storage.
     *
     * @param string $owner Repository owner/organization name
     * @param string $repo Repository name
     * @return string
     * @throws Exception
     */
    public function pullCode(string $owner, string $repo): string
    {
        try {
            $user = Auth::user();

            if (!$user instanceof Users) {
                throw new Exception('User not authenticated');
            }

            $githubToken = $user->githubToken()->first();

            if (!$githubToken) {
                throw new Exception('GitHub token not found for user');
            }

            $client = new GitHubClient();
            $client->authenticate($githubToken->access_token, null, GitHubClient::AUTH_ACCESS_TOKEN);

            $repositoryContents = $client->api('repo')->contents()->show($owner, $repo, '');

            foreach ($repositoryContents as $file) {
                if ($file['type'] === 'file') {
                    $fileContent = $client->api('repo')->contents()->show($owner, $repo, $file['path']);
                    Storage::put("repositories/{$owner}/{$repo}/" . $file['name'], base64_decode($fileContent['content']));
                }
            }

            return "Code from repository '{$owner}/{$repo}' has been successfully pulled to storage.";
        } catch (Exception $e) {
            throw new Exception('Failed to pull code: ' . $e->getMessage());
        }
    }
}
