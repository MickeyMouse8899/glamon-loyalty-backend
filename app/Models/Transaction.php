<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'brand_id', 'invoice_number',
        'amount', 'points_earned', 'source',
        'status', 'wc_order_id', 'meta'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function pointLedger()
    {
        return $this->hasOne(PointLedger::class);
    }
}