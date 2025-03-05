<?php

namespace App\DataTransferObjects;

class RepositoryDto extends DataModel
{
    /**
     * @param string $owner Repository owner/organization name
     * @param string $repo Repository name
     */
    public function __construct(
        public readonly string $owner,
        public readonly string $repo,
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
            owner: (string)$data['owner'],
            repo: (string)$data['repo'],
        );
    }
}
