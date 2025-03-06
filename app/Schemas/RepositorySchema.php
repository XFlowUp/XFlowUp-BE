<?php

namespace App\Schemas;

/**
 * @OA\Schema(
 *     schema="Repository",
 *     title="Repository",
 *     description="GitHub repository model",
 *     @OA\Property(property="id", type="integer", example=123456789),
 *     @OA\Property(property="name", type="string", example="repo-name"),
 *     @OA\Property(property="full_name", type="string", example="octocat/repo-name"),
 *     @OA\Property(property="description", type="string", nullable=true, example="This is a sample repository"),
 *     @OA\Property(property="private", type="boolean", example=false),
 *     @OA\Property(property="html_url", type="string", example="https://github.com/octocat/repo-name"),
 *     @OA\Property(property="language", type="string", nullable=true, example="PHP"),
 *     @OA\Property(property="stargazers_count", type="integer", example=42),
 *     @OA\Property(property="watchers_count", type="integer", example=42),
 *     @OA\Property(property="forks_count", type="integer", example=23),
 *     @OA\Property(property="open_issues_count", type="integer", example=5),
 *     @OA\Property(property="default_branch", type="string", example="main"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2022-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2022-01-02T00:00:00Z"),
 *     @OA\Property(property="pushed_at", type="string", format="date-time", example="2022-01-03T00:00:00Z")
 * )
 */
class RepositorySchema {}
