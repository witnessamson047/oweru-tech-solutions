<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A business found by a discovery run that has NO website at all — the
 * "we'll build you one" outreach list. These never enter the scrape/scan
 * pipeline (there is nothing to scrape); staff work them by hand and mark
 * them contacted here. Deduped by (business_name, city) so repeated runs of
 * the same city never pile up duplicates.
 */
class DiscoveryLead extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';

    protected $fillable = [
        'discovery_run_id', 'business_name', 'city', 'category', 'osm_type',
        'lat', 'lon', 'status', 'contacted_at', 'notes',
    ];

    protected $casts = [
        'lat' => 'double',
        'lon' => 'double',
        'contacted_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(DiscoveryRun::class, 'discovery_run_id');
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function markContacted(): void
    {
        $this->update([
            'status' => self::STATUS_CONTACTED,
            'contacted_at' => now(),
        ]);
    }
}
