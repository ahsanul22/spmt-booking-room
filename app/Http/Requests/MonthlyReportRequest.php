<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MonthlyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('access-pic');
    }

    public function rules(): array
    {
        return [
            'month' => ['sometimes', 'required', 'date_format:Y-m', 'after_or_equal:1000-01', 'before_or_equal:9998-12'],
            'branch_id' => [Rule::prohibitedIf(! $this->user()->can('access-admin')), 'nullable', 'integer', 'exists:branches,id'],
            'room_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
