<?php

namespace App\Http\Requests;

class DecideBookingRequest extends BranchFilterRequest
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->user()->can('access-pic');
    }

    protected function prepareForValidation(): void
    {
        // Retain the legacy rejection_reason field used by the detail form.
        if ($this->has('decision_notes')) {
            $this->merge(['rejection_reason' => $this->input('decision_notes')]);
        }
    }

    public function rules(): array
    {
        return parent::rules() + [
            'approved_it_support' => ['sometimes', 'boolean'],
            'approved_consumption_snack' => ['sometimes', 'boolean'],
            'approved_consumption_meal' => ['sometimes', 'boolean'],
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'decision_notes' => ['nullable', 'string', 'max:2000'],
            'return_to' => ['nullable', 'string', 'in:list,detail'],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 2.000 karakter.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + [
            'decision' => 'keputusan', 'decision_notes' => 'catatan keputusan', 'rejection_reason' => 'alasan penolakan',
            'approved_it_support' => 'persetujuan teknisi IT', 'approved_consumption_snack' => 'persetujuan snack',
            'approved_consumption_meal' => 'persetujuan makanan', 'return_to' => 'halaman tujuan',
        ];
    }
}
