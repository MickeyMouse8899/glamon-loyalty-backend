<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointLedger extends Model
{
    protected $table = 'points_ledger';

    protected $fillable = [
        'user_id', 'brand_id', 'transaction_id',
        'points', 'type', 'description', 'balance_after'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
