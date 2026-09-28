<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapeWatchEvent extends Model
{
    public const TYPE_RATING_DROP = 'rating_drop';
    public const TYPE_NEW_REVIEW = 'new_review';
    public const TYPE_CONTACT_CHANGED = 'contact_changed';
    public const TYPE_SERVICES_CHANGED = 'services_changed';
    public const TYPE_SITE_DOWN = 'site_down';
    public const TYPE_BACK_ONLINE = 'back_online';

    public const SEVERITY_INFO = 1;
    public const SEVERITY_NOTEWORTHY = 2;
    public const SEVERITY_HOT = 3;

    protected $fillable = [
        'scraped_business_id',
        'type',
        'severity',
        'changes',
        'notified_at',
    ];

    protected $casts = [
        'severity' => 'integer',
        'changes' => 'array',
        'notified_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(ScrapedBusiness::class, 'scraped_business_id');
    }

    public function isHot(): bool
    {
        return $this->severity >= self::SEVERITY_HOT;
    }

    public function scopeHot($query)
    {
        return $query->where('severity', '>=', self::SEVERITY_HOT);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Human label for badges in the admin UI (business page + dashboard feed).
     */
    public function getLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_RATING_DROP => '▼ Rating drop',
            self::TYPE_NEW_REVIEW => '★ New review',
            self::TYPE_CONTACT_CHANGED => '✎ Contact changed',
            self::TYPE_SERVICES_CHANGED => '≡ Services changed',
            self::TYPE_SITE_DOWN => '⊘ Site down',
            self::TYPE_BACK_ONLINE => '✓ Back online',
            default => $this->type,
        };
    }

    /**
     * Severity-based badge styling class shared by all watchdog views.
     */
    public function getStyleAttribute(): string
    {
        return match (true) {
            $this->severity >= self::SEVERITY_HOT => 'bg-red-50 text-red-700 border-red-200',
            $this->severity >= self::SEVERITY_NOTEWORTHY => 'bg-blue-50 text-blue-700 border-blue-200',
            default => 'bg-gray-50 text-gray-600 border-gray-200',
        };
    }

    /**
     * One-line human summary of what changed (from the changes payload).
     */
    public function getSummaryAttribute(): string
    {
        $c = $this->changes ?? [];

        return match ($this->type) {
            self::TYPE_RATING_DROP => sprintf('Rating %s → %s/5', $c['rating_avg']['from'] ?? '?', $c['rating_avg']['to'] ?? '?'),
            self::TYPE_NEW_REVIEW => trim(sprintf(
                '%s%s%s',
                isset($c['rating']) ? str_repeat('★', (int) round((float) $c['rating'])) . ' ' : '',
                '"' . ($c['text'] ?? '') . '"',
                !empty($c['author']) ? ' — ' . $c['author'] : '',
            )),
            self::TYPE_CONTACT_CHANGED, self::TYPE_SERVICES_CHANGED => sprintf(
                '%s: %s → %s',
                $c['field'] ?? '?',
                $c['from'] ?? '',
                $c['to'] ?? ''
            ),
            default => (string) ($c['message'] ?? ''),
        };
    }
}
