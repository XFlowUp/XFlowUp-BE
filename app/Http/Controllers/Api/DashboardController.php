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
                    'current_page' => $repositories['current_page'],
                    'per_page' => $repositories['per_page'],
                    'total' => $repositories['total'],
                    'last_page' => $repositories['last_page'],
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
            $dto = RepositoryDto::fromRequest($request->validated());

            $repository = $this->repositoryService->getRepository($dto->owner, $dto->repo);
            return response()->json([
                'success' => true,
                'data' => $repository['data']
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
}
