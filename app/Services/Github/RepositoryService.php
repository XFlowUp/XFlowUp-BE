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
     * Get authenticated user or throw unauthorized exception
     * 
     * @return Users
     * @throws \Illuminate\Auth\AuthenticationException
     */
    private function getAuthenticatedUser(): Users
    {
        $user = Auth::user();

        if (!$user instanceof Users) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthorized: User not authenticated');
        }

        return $user;
    }

    /**
     * Get user's GitHub token or throw exception
     * 
     * @param Users $user
     * @return object
     * @throws Exception
     */
    private function getUserGithubToken(Users $user)
    {
        $githubToken = $user->githubToken()->first();

        if (!$githubToken) {
            throw new Exception('GitHub token not found for user');
        }

        return $githubToken;
    }

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
            // Get authenticated user
            $user = $this->getAuthenticatedUser();

            $perPage = $perPage ?: $this->perPage;
            $cacheKey = "user_{$user->id}_repositories_page_{$page}_perpage_{$perPage}_sort_{$sort}_dir_{$direction}";

            // Use cache duration from config
            return Cache::remember(
                $cacheKey,
                now()->addMinutes(config('cache-settings.repositories_list_duration', 30)),
                function () use ($user, $page, $perPage, $sort, $direction) {
                    $githubToken = $this->getUserGithubToken($user);

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
                }
            );
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Rethrow authentication exceptions
            throw $e;
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
            // Verify user is authenticated
            $this->getAuthenticatedUser();

            $repository = $this->client->api('repo')->show($owner, $repo);
            return RepositoryDto::fromArray($repository);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Rethrow authentication exceptions
            throw $e;
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
            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            $githubToken = $this->getUserGithubToken($user);

            // If no branch is specified, get the default branch (usually main)
            if ($ref === null) {
                $repository = $this->client->api('repo')->show($owner, $repo);
                $ref = $repository['default_branch'] ?? 'main';
            }

            $fileContent = $this->client->api('repo')->contents()->download($owner, $repo, $path, $ref);

            return $fileContent;
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Rethrow authentication exceptions
            throw $e;
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve file content: ' . $e->getMessage());
        }
    }

    /**
     * Get tree/folder structure from a repository
     *
     * @param string $owner Repository owner/organization name
     * @param string $repo Repository name
     * @param string $path Path to the directory within the repository
     * @param string|null $ref Reference (branch, tag or commit SHA)
     * @return array Files and directories within the specified path
     * @throws Exception
     */
    public function getFolderContent(string $owner, string $repo, string $path = '', ?string $ref = null): array
    {
        try {
            // Get authenticated user
            $user = $this->getAuthenticatedUser();
            $githubToken = $this->getUserGithubToken($user);

            // If no branch is specified, get the default branch (usually main)
            if ($ref === null) {
                $repository = $this->client->api('repo')->show($owner, $repo);
                $ref = $repository['default_branch'] ?? 'main';
            }

            $path = trim($path, '/');

            $contents = $this->client->api('repo')->contents()->show($owner, $repo, $path, $ref);

            return array_map(function ($item) use ($ref) {
                return [
                    'name' => $item['name'],
                    'path' => $item['path'],
                    'size' => $item['size'] ?? 0,
                    'type' => $item['type'], // 'file' or 'dir'
                    'sha' => $item['sha'],
                    'url' => $item['html_url'],
                    'download_url' => $item['download_url'] ?? null,
                    'branch' => $ref // Include the branch information in the response
                ];
            }, $contents);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Rethrow authentication exceptions
            throw $e;
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve folder content: ' . $e->getMessage());
        }
    }

    /**
     * Clear the repository cache for the current user
     * 
     * @return void
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function clearCache(): void
    {
        $user = $this->getAuthenticatedUser();

        $cachePattern = "user_{$user->id}_repositories_*";
        foreach (Cache::get($cachePattern, []) as $key => $value) {
            Cache::forget($key);
        }
    }
}
