<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'floor_number' => ['required', 'integer', 'between:-2147483648,2147483647', Rule::unique('floors')->ignore($this->route('floor'))],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama', 'floor_number' => 'nomor lantai', 'description' => 'deskripsi', 'is_active' => 'status aktif'];
    }
}
