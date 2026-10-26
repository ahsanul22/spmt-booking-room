<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class MonthlyReportRequest extends PaginationRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-pic');
    }

    public function rules(): array
    {
        return parent::rules() + [
            'month' => ['bail', 'sometimes', 'required', 'date_format:Y-m', 'after_or_equal:1000-01', 'before_or_equal:9998-12'],
            'branch_id' => ['bail', Rule::prohibitedIf(! $this->user()->can('access-admin')), 'nullable', 'integer', 'min:1', 'exists:branches,id'],
            'room_id' => ['bail', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['month' => 'bulan laporan', 'room_id' => 'ruangan', 'branch_id' => 'kantor / cabang'];
    }
}
