<?php

namespace App\Services\Github;

use App\DataTransferObjects\Github\RepositoryDto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Github\Client as GitHubClient;
use App\Models\Users;
use Exception;

class RepositoryService
{
    protected int $perPage = 10;

    public function __construct(
        protected GitHubClient $client
    ) {}

    /**
     * Get repositories for the authenticated user with pagination and sorting
     *
     * @param int $page Current page number
     * @param int|null $perPage Items per page
     * @param string $sort Field to sort by (created, updated, pushed, full_name)
     * @param string $direction Sort direction (asc or desc)
     * @return array Repository collection with pagination metadata
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


                // Get all repositories first (for total count)
                // Note: This might be expensive for users with many repositories
                $allRepos = $this->client->api('current_user')->repositories();
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

                // Format repositories as DTOs
                $repositoriesData = array_map(function ($repo) {
                    return $repo instanceof RepositoryDto
                        ? $repo
                        : RepositoryDto::fromArray($repo);
                }, $repositories);

                // Return array with pagination metadata instead of DTO
                return [
                    'data' => $repositoriesData,
                    'currentPage' => $page,
                    'perPage' => $perPage,
                    'total' => $total,
                    'lastPage' => $lastPage
                ];
            });
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve repositories: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific repository by owner and repo name
     * (Supports both public and private repositories the authenticated user has access to)
     *
     * @param string $owner Repository owner/organization name
     * @param string $repo Repository name
     * @return RepositoryDto
     * @throws Exception
     */
    public function getRepository(string $owner, string $repo): RepositoryDto
    {
        try {
            $repository = $this->client->api('repo')->show($owner, $repo);
            return RepositoryDto::fromArray($repository);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                throw new Exception('Repository not found or you do not have permission to access it');
            }
            throw new Exception('Failed to retrieve repository: ' . $e->getMessage());
        }
    }

    /**
     * Get file content from a repository by file path
     *
     * @param string $owner Repository owner/organization name
     * @param string $repo Repository name
     * @param string $path Path to the file
     * @param string|null $ref Reference (branch, tag or commit SHA)
     * @return string File content
     * @throws Exception
     */
    public function getFileContent(string $owner, string $repo, string $path, ?string $ref = null): string
    {
        try {
            $fileContent = $this->client->api('repo')->contents()->download($owner, $repo, $path, $ref);

            return $fileContent;
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve file content: ' . $e->getMessage());
        }
    }

    /**
     * Clear the repository cache for the current user
     * 
     * @return void
     */
    public function clearCache(): void
    {
        $user = Auth::user();
        if ($user instanceof Users) {
            $cachePattern = "user_{$user->id}_repositories_*";
            foreach (Cache::get($cachePattern, []) as $key => $value) {
                Cache::forget($key);
            }
        }
    }
}
