<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    public function rules(): array
    {
        $record = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($record)],
            'role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ROOM_PIC, User::ROLE_SUPER_ADMIN])],
            'organizational_unit_id' => ['nullable', 'integer', 'exists:organizational_units,id'],
            'is_active' => ['required', 'boolean'],
            'password' => [$record ? 'nullable' : 'required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama', 'organizational_unit_id' => 'unit kerja', 'is_active' => 'status aktif'];
    }
}
