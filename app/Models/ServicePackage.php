<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePackage extends Model
{
    protected $fillable = [
        'name', 'slug', 'group', 'description', 'service_line_id',
        'price_tzs', 'price_usd', 'delivery_days',
        'is_featured', 'active', 'sort_order',
    ];

    public function serviceLine(): BelongsTo
    {
        return $this->belongsTo(ServiceLine::class);
    }

    protected $casts = [
        'price_tzs' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'is_featured' => 'boolean',
        'active' => 'boolean',
    ];

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
