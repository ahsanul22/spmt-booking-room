<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EligibleRoomPic implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false ||
            ! User::query()->whereKey($value)->where('role', User::ROLE_ROOM_PIC)->exists()) {
            $fail('PIC harus merupakan user dengan role room_pic.');
        }
    }
}
