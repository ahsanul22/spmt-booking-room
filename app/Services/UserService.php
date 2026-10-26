<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function save(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if ($user->exists) {
                $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            }
            $user->name = $data['name'];
            $user->email = $data['email'];
            // Privileged values and location permissions are only written after Admin authorization.
            $user->role = $data['role'];
            $user->organizational_unit_id = $data['organizational_unit_id'] ?? null;
            $user->is_active = $data['is_active'];
            $user->is_receptionist = in_array($data['role'], [User::ROLE_USER, User::ROLE_ROOM_PIC], true)
                && (bool) ($data['is_receptionist'] ?? $user->is_receptionist);
            if (array_key_exists('whatsapp_number', $data)) {
                $user->whatsapp_number = $data['whatsapp_number'];
            }
            if (! empty($data['password'])) {
                $this->setPassword($user, $data['password']);
            }
            $user->save();
            if (array_key_exists('branch_ids', $data)) {
                $user->branches()->sync($data['branch_ids']);
            }

            return $user;
        });
    }

    public function resetPassword(User $user, string $password): void
    {
        DB::transaction(function () use ($user, $password) {
            $record = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->setPassword($record, $password);
            $record->save();
        });
    }

    private function setPassword(User $user, string $password): void
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
    }
}
