<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo_url', 'primary_color', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function userProfiles()
    {
        return $this->hasMany(UserBrandProfile::class);
    }

    public function pointRules()
    {
        return $this->hasMany(BrandPointRule::class);
    }

    public function tierRules()
    {
        return $this->hasMany(TierRule::class)->orderBy('min_points');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function rewards()
    {
        return $this->hasMany(Reward::class);
    }

    public function webstoreIntegration()
    {
        return $this->hasOne(WebstoreIntegration::class);
    }
}
