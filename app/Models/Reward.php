<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = [
        'brand_id', 'name', 'description', 'image_url',
        'points_required', 'stock', 'unlimited_stock',
        'is_active', 'valid_until'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unlimited_stock' => 'boolean',
        'valid_until' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }
}