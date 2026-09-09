<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enquiry extends Model
{
    protected $fillable = [
        'name', 'business_name', 'email', 'phone', 'country',
        'package_id', 'package_name', 'problem_description', 'current_cost',
        'budget_range', 'required_date',
        'stage', 'source', 'owner_id', 'notes', 'last_stage_changed_at',
        'consent_given',
    ];

    protected $casts = [
        'required_date' => 'date',
        'last_stage_changed_at' => 'datetime',
        'consent_given' => 'boolean',
    ];

    public const STAGES = [
        'new', 'qualified', 'diagnostic_paid',
        'proposal_sent', 'won', 'lost',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function scan(): HasOne
    {
        return $this->hasOne(Scan::class);
    }

    public function website(): HasOne
    {
        return $this->hasOne(Website::class);
    }

    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
