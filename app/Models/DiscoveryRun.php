<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One execution of the OSM/Overpass discovery probe (scanner/osm_discovery.py):
 * a city + category + limit in, a list of business website URLs out.
 * The Overpass query itself is NOT stored — the probe owns it — but the run
 * keeps the query inputs, the probe's stats and result payloads, and the
 * python stderr (if the probe failed) for auditing and debugging.
 */
class DiscoveryRun extends Model
{
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const SOURCE_ADMIN = 'admin';   // triggered from the admin UI form
    public const SOURCE_COMMAND = 'command'; // php artisan discovery:run

    protected $fillable = [
        'city', 'category', 'limit', 'status', 'source', 'user_id',
        'stats', 'result', 'error', 'queued_count', 'skipped_count', 'leads_count', 'finished_at',
    ];

    protected $casts = [
        'stats' => 'array',
        'result' => 'array',
        'finished_at' => 'datetime',
        'limit' => 'integer',
        'queued_count' => 'integer',
        'skipped_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(ScrapeTarget::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(DiscoveryLead::class, 'discovery_run_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
