<?php

namespace Tests\Unit;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAccountAccessTest extends TestCase
{
    public function test_api_identity_requires_an_active_known_role_and_never_exposes_credentials(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        foreach ([User::ROLE_USER, User::ROLE_ROOM_PIC, User::ROLE_SUPER_ADMIN] as $role) {
            $user = (new User)->forceFill(['id' => 123, 'name' => 'API User', 'role' => $role, 'is_active' => true,
                'password' => 'hidden-password', 'remember_token' => 'hidden-token']);
            Sanctum::actingAs($user);
            $this->getJson('/api/user')->assertOk()->assertJsonPath('name', 'API User')
                ->assertJsonMissingPath('password')->assertJsonMissingPath('remember_token');
            $user->is_active = false;
            $this->getJson('/api/user')->assertForbidden();
        }
        Sanctum::actingAs((new User)->forceFill(['id' => 124, 'role' => 'unknown', 'is_active' => true]));
        $this->getJson('/api/user')->assertForbidden();
    }
}
