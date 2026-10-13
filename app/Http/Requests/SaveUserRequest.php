<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\WhatsappNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
        if ($this->has('whatsapp_number') && (is_string($this->input('whatsapp_number')) || $this->input('whatsapp_number') === null)) {
            $this->merge(['whatsapp_number' => WhatsappNumber::normalize($this->input('whatsapp_number'))]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    public function rules(): array
    {
        $record = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', function ($attribute, $value, $fail) use ($record) {
                if (User::whereRaw('lower(btrim(email)) = ?', [$value])->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists()) {
                    $fail('Email sudah digunakan oleh akun lain.');
                }
            }],
            'whatsapp_number' => ['bail', 'sometimes', 'nullable', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            'role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ROOM_PIC, User::ROLE_SUPER_ADMIN])],
            'organizational_unit_id' => ['nullable', 'integer', 'exists:organizational_units,id'],
            'is_active' => ['required', 'boolean'],
            'password' => [$record ? 'nullable' : 'required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama', 'whatsapp_number' => 'nomor WhatsApp', 'organizational_unit_id' => 'unit kerja', 'is_active' => 'status aktif'];
    }
}
