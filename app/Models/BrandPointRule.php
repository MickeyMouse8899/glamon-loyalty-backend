<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandPointRule extends Model
{
    protected $fillable = [
        'brand_id', 'source', 'rp_per_point',
        'multiplier', 'min_transaction', 'is_active',
        'valid_from', 'valid_until'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'multiplier' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}