<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-general') ?? false;
    }

    public function rules(): array
    {
        // Keep the SQL offset within bigint even for the largest supported page size.
        return ['page' => ['bail', 'nullable', 'integer', 'between:1,2147483647']];
    }

    public function attributes(): array
    {
        return ['page' => 'halaman'];
    }
}
