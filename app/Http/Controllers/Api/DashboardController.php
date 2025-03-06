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

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="GitHub Integration API",
 *      description="API for interacting with GitHub repositories",
 *      @OA\Contact(
 *          email="admin@example.com"
 *      )
 * )
 * 
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth",
 * )
 */
class DashboardController extends Controller
{

    public function __construct(protected RepositoryService $repositoryService, protected RepositoryActionService $repositoryActionService) {}

    /**
     * @OA\Get(
     *     path="/api/dashboard/repositories",
     *     operationId="getRepositories",
     *     tags={"Repositories"},
     *     summary="Get list of repositories",
     *     description="Returns a paginated list of repositories for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Field to sort by (created, updated, pushed, full_name)",
     *         required=false,
     *         @OA\Schema(type="string", default="updated")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Repository")),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="last_page", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/dashboard/repository",
     *     operationId="getRepository",
     *     tags={"Repositories"},
     *     summary="Get specific repository details",
     *     description="Returns details of a specific repository",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner",
     *         in="query",
     *         description="Repository owner/organization name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="repo",
     *         in="query",
     *         description="Repository name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Repository")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Repository not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/dashboard/clear-repository-cache",
     *     operationId="clearRepositoryCache",
     *     tags={"Repositories"},
     *     summary="Clear repository cache",
     *     description="Clears the cached repository data for the current user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cache cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Repository cache cleared successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/dashboard/pull-repository-code",
     *     operationId="pullRepositoryCode",
     *     tags={"Repositories"},
     *     summary="Pull repository code",
     *     description="Pulls code from a specific repository",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"owner","repo"},
     *             @OA\Property(property="owner", type="string", example="octocat"),
     *             @OA\Property(property="repo", type="string", example="hello-world")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code pulled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Repository code pulled successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Repository not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/api/dashboard/repository/{owner}/{repo}/file",
     *     operationId="getRepositoryFile",
     *     tags={"Repositories"},
     *     summary="Get file content from repository",
     *     description="Returns content of a specific file from a repository",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner",
     *         in="path",
     *         description="Repository owner/organization name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="repo",
     *         in="path",
     *         description="Repository name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="filepath",
     *         in="query",
     *         description="Path to the file within the repository",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="ref",
     *         in="query",
     *         description="Reference (branch, tag, or commit SHA)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="content", type="string", example="Content of the file"),
     *                 @OA\Property(property="path", type="string", example="README.md"),
     *                 @OA\Property(property="repo", type="string", example="hello-world"),
     *                 @OA\Property(property="owner", type="string", example="octocat"),
     *                 @OA\Property(property="ref", type="string", example="main")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Missing filepath parameter"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="File or repository not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getRepositoryFile(string $owner, string $repo, Request $request): JsonResponse
    {
        try {
            $path = $request->input('filepath');
            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'File path is required'
                ], 400);
            }

            // Use the ref query parameter or null to use the default branch
            $ref = $request->input('ref');
            $fileContent = $this->repositoryService->getFileContent($owner, $repo, $path, $ref);

            // Get the actual branch used (if ref was null, it will be the default branch)
            $usedBranch = $ref;
            if ($ref === null) {
                $repository = $this->repositoryService->getRepository($owner, $repo);
                $usedBranch = $repository->default_branch ?? 'main';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $fileContent,
                    'path' => $path,
                    'repo' => $repo,
                    'owner' => $owner,
                    'branch' => $usedBranch
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'path' => $path ?? null,
                'repo' => $repo,
                'owner' => $owner,
                'ref' => $ref ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/repository/{owner}/{repo}/folder",
     *     operationId="getRepositoryFolder",
     *     tags={"Repositories"},
     *     summary="Get folder contents from repository",
     *     description="Returns list of files and folders within a specified directory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner",
     *         in="path",
     *         description="Repository owner/organization name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="repo",
     *         in="path",
     *         description="Repository name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="path",
     *         in="query",
     *         description="Path to the directory within the repository",
     *         required=false,
     *         @OA\Schema(type="string", default="")
     *     ),
     *     @OA\Parameter(
     *         name="ref",
     *         in="query",
     *         description="Reference (branch, tag, or commit SHA)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="README.md"),
     *                     @OA\Property(property="path", type="string", example="src/README.md"),
     *                     @OA\Property(property="size", type="integer", example=1234),
     *                     @OA\Property(property="type", type="string", example="file", enum={"file", "dir"}),
     *                     @OA\Property(property="sha", type="string", example="abc123def456"),
     *                     @OA\Property(property="url", type="string", example="https://github.com/octocat/hello-world/blob/main/README.md"),
     *                     @OA\Property(property="download_url", type="string", nullable=true, example="https://raw.githubusercontent.com/octocat/hello-world/main/README.md")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Repository or folder not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function getRepositoryFolder(string $owner, string $repo, Request $request): JsonResponse
    {
        try {
            $path = $request->input('path', '');

            // Use the ref query parameter or null to use the default branch
            $ref = $request->input('ref');

            $folderContent = $this->repositoryService->getFolderContent($owner, $repo, $path, $ref);

            // Get the actual branch used (if ref was null, it will be the default branch)
            $usedBranch = $ref;
            if ($ref === null) {
                $repository = $this->repositoryService->getRepository($owner, $repo);
                $usedBranch = $repository->default_branch ?? 'main';
            }

            return response()->json([
                'success' => true,
                'data' => $folderContent,
                'path' => $path,
                'repo' => $repo,
                'owner' => $owner,
                'branch' => $usedBranch
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'path' => $path ?? '',
                'repo' => $repo,
                'owner' => $owner,
                'ref' => $ref ?? null,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
