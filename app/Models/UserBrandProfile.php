<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBrandProfile extends Model
{
    protected $fillable = [
        'user_id', 'brand_id', 'member_code',
        'total_points', 'tier', 'joined_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'total_points' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}