<?php

namespace App\Http\Requests;

use App\Rules\EligibleRoomPic;
use Illuminate\Foundation\Http\FormRequest;

class SaveRoomPicsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('pic_ids')) {
            $this->merge(['pic_ids' => []]);
        }
    }

    public function rules(): array
    {
        return ['pic_ids' => ['present', 'array'], 'pic_ids.*' => ['bail', 'required', 'integer', 'distinct', new EligibleRoomPic]];
    }
}
