<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class WhatsappHistoryRequest extends PaginationRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return parent::rules() + [
            'event' => ['nullable', 'string', Rule::in(['approval_request', 'consumption'])],
            'status' => ['nullable', 'string', Rule::in(['pending', 'processing', 'accepted', 'failed', 'unknown', 'skipped'])],
        ];
    }
}
