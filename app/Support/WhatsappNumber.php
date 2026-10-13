<?php

namespace App\Support;

class WhatsappNumber
{
    public static function normalize(?string $value): ?string
    {
        $number = preg_replace('/[\s().-]/', '', trim($value ?? ''));
        if ($number === '') {
            return null;
        }
        if (str_starts_with($number, '0')) {
            return '+62'.substr($number, 1);
        }

        return str_starts_with($number, '62') ? '+'.$number : $number;
    }

    public static function valid(?string $number): bool
    {
        return is_string($number) && preg_match('/^\+[1-9][0-9]{7,14}$/', $number) === 1;
    }
}
