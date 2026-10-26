<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['bail', 'required', 'string', 'max:255', Rule::unique('branches')->ignore($this->route('branch'))]];
    }

    public function attributes(): array
    {
        return ['name' => 'nama kantor / cabang'];
    }
}
