<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOrganizationalUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['directorate', 'division', 'department'])],
            'parent_id' => ['nullable', 'integer', 'exists:organizational_units,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama unit', 'type' => 'tipe unit', 'parent_id' => 'parent unit', 'is_active' => 'status aktif'];
    }
}
