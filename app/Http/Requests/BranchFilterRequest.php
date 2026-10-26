<?php

namespace App\Http\Requests;

class BranchFilterRequest extends PaginationRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && (! $this->filled('branch_id') || $this->user()->can('access-admin'));
    }

    public function rules(): array
    {
        return parent::rules() + ['branch_id' => ['bail', 'nullable', 'integer', 'min:1', 'exists:branches,id']];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['branch_id' => 'kantor / cabang'];
    }
}
