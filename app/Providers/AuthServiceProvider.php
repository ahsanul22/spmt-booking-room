<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $areas = [
            'access-general' => [User::ROLE_USER, User::ROLE_ROOM_PIC, User::ROLE_SUPER_ADMIN],
            'access-employee' => [User::ROLE_USER, User::ROLE_ROOM_PIC],
            'access-pic' => [User::ROLE_ROOM_PIC, User::ROLE_SUPER_ADMIN],
            'access-admin' => [User::ROLE_SUPER_ADMIN],
        ];

        foreach ($areas as $ability => $roles) {
            Gate::define($ability, fn (User $user): bool => $user->is_active && in_array($user->role, $roles, true));
        }
    }
}
