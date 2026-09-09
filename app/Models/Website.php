<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Website extends Model
{
    protected $fillable = [
        'business_name', 'url', 'sector', 'status',
        'exclusion_status', 'exclusion_reason', 'enquiry_id',
    ];

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function latestScan(): HasOne
    {
        return $this->hasOne(Scan::class)->latestOfMany();
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('exclusion_status', '!=', 'excluded');
    }
}
