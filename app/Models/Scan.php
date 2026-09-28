<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scan extends Model
{
    protected $fillable = [
        'website_id', 'url', 'started_at', 'completed_at',
        'status', 'score', 'band', 'source', 'error_message',
        'enquiry_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'pagespeed' => 'array',
        'ai_insight' => 'array',
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

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'paymentable_id')
            ->where('paymentable_type', self::class);
    }

    public function previousScan(): ?self
    {
        if (!$this->website_id) {
            return null;
        }

        return self::where('website_id', $this->website_id)
            ->where('id', '<', $this->id)
            ->where('status', 'completed')
            ->latest()
            ->first();
    }

    public function scoreChange(): ?int
    {
        $prev = $this->previousScan();
        if (!$prev || is_null($prev->score) || is_null($this->score)) {
            return null;
        }

        return $this->score - $prev->score;
    }

    public function report(): HasOne
    {
        return $this->hasOne(Report::class);
    }

    /**
     * Real-world mobile performance measurement (component 4, External API),
     * or null when the external measurement was unavailable/disabled.
     */
    public function pagespeedMetric(string $key): mixed
    {
        return $this->pagespeed['metrics'][$key] ?? null;
    }

    public static function calculateBand(int $score): string
    {
        if ($score < 40) return 'Critical';
        if ($score < 60) return 'Weak';
        if ($score < 80) return 'Adequate';
        return 'Strong';
    }
}
