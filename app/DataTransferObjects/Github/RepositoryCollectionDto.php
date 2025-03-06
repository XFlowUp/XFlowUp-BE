<?php

namespace App\DataTransferObjects\Github;

class RepositoryCollectionDto
{
    public array $data;
    public int $currentPage;
    public int $perPage;
    public int $total;
    public int $lastPage;

    public function __construct(array $data, int $currentPage, int $perPage, int $total, int $lastPage)
    {
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->lastPage = $lastPage;

        $this->data = array_map(function ($repository) {
            return $repository instanceof RepositoryDto
                ? $repository
                : RepositoryDto::fromArray($repository);
        }, $data);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['data'] ?? [],
            $data['current_page'] ?? 1,
            $data['per_page'] ?? 10,
            $data['total'] ?? 0,
            $data['last_page'] ?? 1
        );
    }
}
