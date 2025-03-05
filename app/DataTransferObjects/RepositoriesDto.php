<?php

namespace App\DataTransferObjects;

class RepositoriesDto extends DataModel
{
    /**
     * @param int $page Current page number
     * @param int|null $perPage Number of items per page
     * @param string $sort Field to sort by (created, updated, pushed, full_name)
     * @param string $direction Sort direction (asc or desc)
     */
    public function __construct(
        public readonly int $page = 1,
        public readonly ?int $perPage = null,
        public readonly string $sort = 'updated',
        public readonly string $direction = 'desc',
    ) {}

    /**
     * Create a DTO from HTTP request data
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            page: (int)($data['page'] ?? 1),
            perPage: isset($data['per_page']) ? (int)$data['per_page'] : null,
            sort: in_array($data['sort'] ?? '', ['created', 'updated', 'pushed', 'full_name']) ? $data['sort'] : 'updated',
            direction: in_array($data['direction'] ?? '', ['asc', 'desc']) ? $data['direction'] : 'desc',
        );
    }
}
