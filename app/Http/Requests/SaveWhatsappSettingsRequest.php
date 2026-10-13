<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\BookingWhatsapp;
use App\Support\WhatsappNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveWhatsappSettingsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['_event' => $this->route('template')->event]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'include_room_pics' => ['required', 'boolean'],
            'body' => ['required', 'string', 'max:2000'],
            'recipient_ids' => ['sometimes', 'array', 'max:100'],
            'recipient_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where('is_active', true)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            preg_match_all('/\{([^{}]*)\}/', $this->input('body'), $matches);
            if (array_diff($matches[1], BookingWhatsapp::VARIABLES)) {
                $validator->errors()->add('body', 'Variabel template tidak dikenal. Gunakan variabel yang tersedia.');
            }
            $event = $this->route('template')->event;
            if ($event === 'consumption' && $this->boolean('include_room_pics')) {
                $validator->errors()->add('include_room_pics', 'PIC otomatis hanya untuk pengajuan approval.');
            }
            $ids = $this->input('recipient_ids', []);
            $users = User::whereKey($ids)->get();
            foreach ($users as $user) {
                if (! WhatsappNumber::valid($user->whatsapp_number) || ($event === 'approval_request' && $user->role !== User::ROLE_SUPER_ADMIN)) {
                    $validator->errors()->add('recipient_ids', 'Penerima harus aktif dengan nomor WhatsApp valid. Penerima tambahan approval harus Admin.');
                    break;
                }
            }
            if ($this->boolean('is_active') && ! $ids && ! ($event === 'approval_request' && $this->boolean('include_room_pics'))) {
                $validator->errors()->add('recipient_ids', 'Pilih penerima sebelum mengaktifkan notifikasi.');
            }
        });
    }
}
