<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveRoomAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('organizational_unit_ids')) {
            $this->merge(['organizational_unit_ids' => []]);
        }
    }

    public function rules(): array
    {
        return [
            'organizational_unit_ids' => ['present', 'array'],
            'organizational_unit_ids.*' => ['required', 'integer', 'distinct', 'exists:organizational_units,id'],
        ];
    }
}
