<?php

namespace App\DataTransferObjects\Github;

class RepositoryDto
{
    public int $id;
    public string $name;
    public string $full_name;
    public ?string $description;
    public bool $private;
    public string $html_url;
    public ?string $language;
    public int $stargazers_count;
    public int $watchers_count;
    public int $forks_count;
    public int $open_issues_count;
    public string $default_branch;
    public ?string $created_at;
    public ?string $updated_at;
    public ?string $pushed_at;

    public function __construct(
        int $id,
        string $name,
        string $full_name,
        ?string $description,
        bool $private,
        string $html_url,
        ?string $language,
        int $stargazers_count,
        int $watchers_count,
        int $forks_count,
        int $open_issues_count,
        string $default_branch,
        ?string $created_at,
        ?string $updated_at,
        ?string $pushed_at
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->full_name = $full_name;
        $this->description = $description;
        $this->private = $private;
        $this->html_url = $html_url;
        $this->language = $language;
        $this->stargazers_count = $stargazers_count;
        $this->watchers_count = $watchers_count;
        $this->forks_count = $forks_count;
        $this->open_issues_count = $open_issues_count;
        $this->default_branch = $default_branch;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->pushed_at = $pushed_at;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? 0,
            $data['name'] ?? '',
            $data['full_name'] ?? '',
            $data['description'] ?? null,
            $data['private'] ?? false,
            $data['html_url'] ?? '',
            $data['language'] ?? null,
            $data['stargazers_count'] ?? 0,
            $data['watchers_count'] ?? 0,
            $data['forks_count'] ?? 0,
            $data['open_issues_count'] ?? 0,
            $data['default_branch'] ?? 'main',
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null,
            $data['pushed_at'] ?? null
        );
    }
}
