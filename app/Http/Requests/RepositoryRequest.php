<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepositoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Auth is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return match ($this->route()->getName()) {
            'dashboard.repositories' => $this->getRepositoriesRules(),
            'dashboard.repository' => $this->getRepositoryRules(),
            default => [],
        };
    }

    /**
     * Get validation rules for repositories list endpoint
     *
     * @return array<string, mixed>
     */
    protected function getRepositoriesRules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort' => 'nullable|string|in:created,updated,pushed,full_name',
            'direction' => 'nullable|string|in:asc,desc',
        ];
    }

    /**
     * Get validation rules for single repository endpoint
     *
     * @return array<string, mixed>
     */
    protected function getRepositoryRules(): array
    {
        return [
            'owner' => 'required|string',
            'repo' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Page must be a number.',
            'page.min' => 'Page must be at least 1.',
            'per_page.integer' => 'Items per page must be a number.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            'sort.in' => 'Sort must be one of: created, updated, pushed, or full_name.',
            'direction.in' => 'Direction must be either asc or desc.',
            'owner.required' => 'Repository owner is required.',
            'repo.required' => 'Repository name is required.',
        ];
    }
}
