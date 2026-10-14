<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_ROOM_PIC = 'room_pic';

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public function roleLabel(): string
    {
        return $this->role === self::ROLE_SUPER_ADMIN ? 'Admin' : str_replace('_', ' ', $this->role);
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            self::ROLE_USER => 'dashboard',
            self::ROLE_ROOM_PIC => 'pic.dashboard',
            self::ROLE_SUPER_ADMIN => 'admin.dashboard',
            default => throw new AuthorizationException,
        };
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function managedRooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_pics');
    }

    public function notificationRecipients(): HasMany
    {
        return $this->hasMany(WhatsappRecipient::class);
    }

    protected ?int $pendingApprovalsCountCache = null;

    public function pendingApprovalsCount(): int
    {
        if ($this->pendingApprovalsCountCache !== null) {
            return $this->pendingApprovalsCountCache;
        }

        if (! $this->can('access-pic')) {
            return $this->pendingApprovalsCountCache = 0;
        }

        return $this->pendingApprovalsCountCache = Booking::query()
            ->where('requires_approval', true)
            ->where('status', 'pending')
            ->when(! $this->can('access-admin'), fn ($query) => $query
                ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $this->id)))
            ->count();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'whatsapp_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
