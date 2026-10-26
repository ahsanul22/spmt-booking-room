<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\GuestRegistration;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuestRegistrationService
{
    public function register(array $data): GuestRegistration
    {
        // Resolve the reception office on the server, never from a hidden input.
        $branch = Branch::query()->when(config('guests.branch_id') !== null,
            fn ($query) => $query->whereKey(config('guests.branch_id')))->orderBy('id')->first();
        if (! $branch) {
            throw ValidationException::withMessages(['registration' => 'Pendataan tamu belum tersedia. Silakan hubungi resepsionis.']);
        }
        $data['branch_id'] = $branch->id;
        $now = CarbonImmutable::now(config('booking.timezone'));
        $expires = $now->addDays(max(1, config('guests.active_days')));
        if (config('guests.expiry_mode') === 'end_of_day') {
            $expires = $now->setTime(17, 0);
            if ($expires->lte($now)) {
                $expires = $expires->addDay();
            }
        }

        // Eloquent serializes timestamps in the application timezone (UTC).
        return GuestRegistration::create($data + ['reference' => Str::upper(Str::random(12)), 'expires_at' => $expires->setTimezone(config('app.timezone'))]);
    }
}
