<?php

namespace App\Services\Github;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Github\Client as GitHubClient;
use App\Models\Users;
use Exception;

class RepositoryService
{
    protected int $perPage = 10;

    /**
     * Get repositories for the authenticated user with pagination and sorting
     *
     * @param int $page Current page number
     * @param int|null $perPage Items per page
     * @param string $sort Field to sort by (created, updated, pushed, full_name)
     * @param string $direction Sort direction (asc or desc)
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getRepositories(
        int $page = 1,
        ?int $perPage = null,
        string $sort = 'updated',
        string $direction = 'desc'
    ): array {
        try {
            // Check if user is logged in
            $user = Auth::user();

            if (!$user instanceof Users) {
                throw new Exception('User not authenticated');
            }

            $perPage = $perPage ?: $this->perPage;
            $cacheKey = "user_{$user->id}_repositories_page_{$page}_perpage_{$perPage}_sort_{$sort}_dir_{$direction}";

            return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($user, $page, $perPage, $sort, $direction) {
                $githubToken = $user->githubToken()->first();

                if (!$githubToken) {
                    throw new Exception('GitHub token not found for user');
                }

                $client = new GitHubClient();
                $client->authenticate($githubToken->access_token, null, GitHubClient::AUTH_ACCESS_TOKEN);

                // Get all repositories first (for total count)
                // Note: This might be expensive for users with many repositories
                $allRepos = $client->api('current_user')->repositories();
                $total = count($allRepos);

                // Apply sorting to all repositories
                if ($sort === 'updated') {
                    usort($allRepos, function ($a, $b) use ($direction) {
                        $result = strtotime($a['updated_at']) <=> strtotime($b['updated_at']);
                        return $direction === 'desc' ? -$result : $result;
                    });
                } elseif ($sort === 'created') {
                    usort($allRepos, function ($a, $b) use ($direction) {
                        $result = strtotime($a['created_at']) <=> strtotime($b['created_at']);
                        return $direction === 'desc' ? -$result : $result;
                    });
                } elseif ($sort === 'pushed') {
                    usort($allRepos, function ($a, $b) use ($direction) {
                        $result = strtotime($a['pushed_at']) <=> strtotime($b['pushed_at']);
                        return $direction === 'desc' ? -$result : $result;
                    });
                } elseif ($sort === 'full_name') {
                    usort($allRepos, function ($a, $b) use ($direction) {
                        $result = strcasecmp($a['full_name'], $b['full_name']);
                        return $direction === 'desc' ? -$result : $result;
                    });
                }

                // Apply pagination
                $offset = ($page - 1) * $perPage;
                $repositories = array_slice($allRepos, $offset, $perPage);

                // Calculate last page
                $lastPage = ceil($total / $perPage);

                // Format response to match what DashboardController expects
                return [
                    'data' => $repositories,
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => $lastPage
                ];
            });
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve repositories: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific repository by owner and repo name
     *
     * @param string $owner Repository owner/organization name
     * @param string $repo Repository name
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getRepository(string $owner, string $repo): array
    {
        try {
            // Check if user is logged in
            $user = Auth::user();

            if (!$user instanceof Users) {
                throw new Exception('User not authenticated');
            }

            $githubToken = $user->githubToken()->first();

            if (!$githubToken) {
                throw new Exception('GitHub token not found for user');
            }

            // Create a new GitHub client instance
            $client = new GitHubClient();
            // Authenticate with the token
            $client->authenticate($githubToken->access_token, null, GitHubClient::AUTH_ACCESS_TOKEN);

            // Get repository using the client
            $repository = $client->api('repo')->show($owner, $repo);

            // Format response to match what controller expects
            return [
                'data' => $repository
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve repository: ' . $e->getMessage());
        }
    }
}
