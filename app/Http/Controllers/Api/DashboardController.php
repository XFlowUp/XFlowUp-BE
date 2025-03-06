<?php

namespace App\Http\Controllers\Api;

use App\DataTransferObjects\RepositoriesDto;
use App\DataTransferObjects\RepositoryDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\RepositoryRequest;
use App\Http\Requests\RepositoryPullRequest;
use App\Services\Github\RepositoryService;
use App\Services\Github\RepositoryActionService;
use Illuminate\Http\JsonResponse;
use Exception;
use App\DataTransferObjects\Github\RepositoryCollectionDto;
use App\DataTransferObjects\Github\RepositoryDto as GithubRepositoryDto;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected RepositoryService $repositoryService;
    protected RepositoryActionService $repositoryActionService;

    public function __construct(RepositoryService $repositoryService, RepositoryActionService $repositoryActionService)
    {
        $this->repositoryService = $repositoryService;
        $this->repositoryActionService = $repositoryActionService;
    }

    public function getRepositories(RepositoryRequest $request): JsonResponse
    {
        try {
            $dto = RepositoriesDto::fromRequest($request->validated());

            $repositories = $this->repositoryService->getRepositories(
                $dto->page,
                $dto->perPage,
                $dto->sort,
                $dto->direction
            );

            return response()->json([
                'success' => true,
                'data' => $repositories['data'],
                'meta' => [
                    'current_page' => $repositories['currentPage'],
                    'per_page' => $repositories['perPage'],
                    'total' => $repositories['total'],
                    'last_page' => $repositories['lastPage'],
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRepository(RepositoryRequest $request): JsonResponse
    {
        try {
            $dto = GithubRepositoryDto::fromRequest($request->validated());

            $repository = $this->repositoryService->getRepository($dto->owner, $dto->repo);
            return response()->json([
                'success' => true,
                'data' => $repository
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function clearRepositoryCache(): JsonResponse
    {
        try {
            $this->repositoryService->clearCache();
            return response()->json([
                'success' => true,
                'message' => 'Repository cache cleared successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function pullRepositoryCode(RepositoryPullRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $owner = $data['owner'];
            $repo = $data['repo'];

            $result = $this->repositoryActionService->pullCode($owner, $repo);
            return response()->json([
                'success' => true,
                'message' => $result
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the content of a file from a repository
     *
     * @param string $owner Repository owner/organization name
     * @param string $repo Repository name
     * @param Request $request Request containing the file path
     * @return JsonResponse
     */
    public function getRepositoryFile(string $owner, string $repo, Request $request): JsonResponse
    {
        try {
            $path = $request->path('filepath');
            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'File path is required'
                ], 400);
            }

            $ref = $request->input('ref');
            $fileContent = $this->repositoryService->getFileContent($owner, $repo, $path, $ref);

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $fileContent,
                    'path' => $path,
                    'repo' => $repo,
                    'owner' => $owner,
                    'ref' => $ref
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'path' => $path,
                'repo' => $repo,
                'owner' => $owner,
                'ref' => $ref,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], 500);
        }
    }
}
