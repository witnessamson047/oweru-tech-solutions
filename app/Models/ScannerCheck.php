<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScannerCheck extends Model
{
    protected $fillable = [
        'name', 'slug', 'area', 'weight',
        'enabled', 'description', 'wording_pass', 'wording_fail',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public const AREAS = [
        'Security', 'Mobile', 'Speed', 'Function',
        'Findability', 'Trust', 'Commerce', 'Freshness',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(ScanResult::class, 'check_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeByArea($query, string $area)
    {
        return $query->where('area', $area);
    }
}
