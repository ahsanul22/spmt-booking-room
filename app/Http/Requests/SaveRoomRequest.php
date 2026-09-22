<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    protected function prepareForValidation(): void
    {
        // Unchecked checkboxes are omitted by HTML; an empty selection clears the pivot.
        if (! $this->exists('facility_ids')) {
            $this->merge(['facility_ids' => []]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('rooms')->ignore($this->route('room'))],
            'floor_id' => ['required', 'integer', 'exists:floors,id'],
            'capacity' => ['required', 'integer', 'between:0,2147483647'],
            'description' => ['nullable', 'string', 'max:5000'],
            'access_type' => ['required', Rule::in(['all', 'restricted'])],
            'requires_approval' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['available', 'maintenance', 'unavailable'])],
            'is_active' => ['required', 'boolean'],
            'facility_ids' => ['present', 'array'],
            'facility_ids.*' => ['required', 'integer', 'distinct', 'exists:facilities,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama ruangan', 'code' => 'kode ruangan', 'floor_id' => 'lantai',
            'capacity' => 'kapasitas', 'description' => 'deskripsi', 'access_type' => 'jenis akses',
            'requires_approval' => 'kebutuhan approval', 'status' => 'status operasional',
            'is_active' => 'status aktif', 'facility_ids' => 'fasilitas', 'facility_ids.*' => 'fasilitas',
        ];
    }
}
