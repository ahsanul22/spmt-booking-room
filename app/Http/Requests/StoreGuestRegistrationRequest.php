<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:50'],
            'purpose' => ['required', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama lengkap', 'organization' => 'instansi', 'contact' => 'nomor kontak', 'nip' => 'NIP', 'purpose' => 'tujuan kunjungan'];
    }
}
