<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return ['confirm_delete' => ['required', 'accepted']];
    }

    public function attributes(): array
    {
        return ['confirm_delete' => 'konfirmasi penghapusan'];
    }
}
