<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TierRule extends Model
{
    protected $fillable = [
        'brand_id', 'tier', 'min_points',
        'max_points', 'color', 'benefits'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
