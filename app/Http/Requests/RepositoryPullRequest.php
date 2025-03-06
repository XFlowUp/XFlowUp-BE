<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepositoryPullRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled through middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'owner' => ['required', 'string', 'max:255'],
            'repo' => ['required', 'string', 'max:255'],
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
            'owner.required' => 'The repository owner is required.',
            'owner.string' => 'The repository owner must be a string.',
            'repo.required' => 'The repository name is required.',
            'repo.string' => 'The repository name must be a string.',
        ];
    }
}
