<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarePlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description',
        'price_tzs', 'price_usd',
        'is_featured', 'active',
    ];

    protected $casts = [
        'price_tzs' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'is_featured' => 'boolean',
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
