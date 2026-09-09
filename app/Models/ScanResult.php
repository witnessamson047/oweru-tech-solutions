<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResult extends Model
{
    protected $fillable = [
        'scan_id', 'check_id', 'check_name', 'area',
        'passed', 'points', 'evidence', 'finding_text', 'consequence',
    ];

    protected $casts = [
        'passed' => 'boolean',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function check(): BelongsTo
    {
        return $this->belongsTo(ScannerCheck::class, 'check_id');
    }

    /**
     * Recommendation mapping for this failed check (finds the Oweru service to sell).
     */
    public function recommendation()
    {
        return $this->hasOne(Recommendation::class, 'check_name', 'check_name')
            ->where('active', true);
    }
}
