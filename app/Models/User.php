<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'google_id',
        'avatar', 'birth_date', 'gender',
        'password', 'is_active', 'fcm_token', 'role'
    ];

    protected $hidden = [
        'password', 'remember_token', 'google_id'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date'        => 'date',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isAdmin(): bool { return in_array($this->role, ['superadmin', 'admin']); }
    public function isKasir(): bool { return in_array($this->role, ['superadmin', 'admin', 'kasir']); }
    public function isMember(): bool { return $this->role === 'member'; }

    public function brandProfiles()
    {
        return $this->hasMany(UserBrandProfile::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function pointLedgers()
    {
        return $this->hasMany(PointLedger::class);
    }

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function getPointsForBrand(int $brandId): int
    {
        return $this->brandProfiles()
            ->where('brand_id', $brandId)
            ->value('total_points') ?? 0;
    }
}
