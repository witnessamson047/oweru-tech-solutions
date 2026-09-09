<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scan extends Model
{
    protected $fillable = [
        'website_id', 'url', 'started_at', 'completed_at',
        'status', 'score', 'band', 'source', 'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ScanResult::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function report(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Report::class);
    }

    public static function calculateBand(int $score): string
    {
        if ($score < 40) return 'Critical';
        if ($score < 60) return 'Weak';
        if ($score < 80) return 'Adequate';
        return 'Strong';
    }
}
