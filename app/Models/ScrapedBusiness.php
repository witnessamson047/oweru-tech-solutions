<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ScrapedBusiness extends Model
{
    protected $fillable = [
        'business_name', 'website_url', 'email', 'phone', 'address',
        'services', 'about', 'source_url', 'status', 'website_id', 'enquiry_id',
        'rating_avg', 'rating_count', 'reviews', 'weaknesses',
        'preliminary_gaps',
        'seen_review_texts',
        'last_scraped_at', 'scrape_count', 'last_scrape_error',
    ];

    public const STATUSES = ['new', 'scanned', 'lead', 'excluded'];

    protected $casts = [
        'rating_avg' => 'decimal:1',
        'reviews' => 'array',
        'weaknesses' => 'array',
        'preliminary_gaps' => 'array',
        'seen_review_texts' => 'array',
        'last_scraped_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function watchEvents(): HasMany
    {
        return $this->hasMany(ScrapeWatchEvent::class, 'scraped_business_id')->latest('id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'excluded');
    }

    /**
     * Preliminary gaps found by the scraper at scrape time (before any health
     * scan): [{check_name, gap, opportunity}, ...].
     */
    public function preliminaryGapCount(): int
    {
        return count($this->preliminary_gaps ?? []);
    }

    /**
     * Outreach priority heuristic from preliminary gaps alone (no scan needed):
     * high = 3+ gaps, medium = 1–2, low = none found by the quick pass.
     */
    public function outreachPriority(): string
    {
        $count = $this->preliminaryGapCount();

        return match (true) {
            $count >= 3 => 'high',
            $count >= 1 => 'medium',
            default => 'low',
        };
    }

    /**
     * Preliminary gaps paired with the mapped Oweru service to sell, using
     * the SAME recommendations table the health scanner maps through
     * (gap.check_name -> recommendations.check_name). Also matches on
     * area-level keywords so a slightly different check name still finds
     * its service.
     *
     * @param Collection|null $recommendations preloaded active recommendations
     *        keyed by check_name — pass one on list pages to avoid per-row queries
     * @return Collection<int, array{gap: array, recommendation: ?Recommendation}>
     */
    public function gapsWithRecommendations(?Collection $recommendations = null): Collection
    {
        $recommendations ??= Recommendation::query()->where('active', true)->get()->keyBy('check_name');

        return collect($this->preliminary_gaps ?? [])->map(function (array $gap) use ($recommendations) {
            $checkName = $gap['check_name'] ?? '';

            $recommendation = $recommendations->get($checkName)
                // Same area AND the gap text overlaps the recommendation's own finding
                ?? $recommendations->first(
                    fn (Recommendation $r) => strcasecmp($r->area, $gap['area'] ?? '') === 0
                )
                // Word-level fallback: any shared significant word
                ?? $recommendations->first(function (Recommendation $r) use ($checkName) {
                    $words = fn (string $s) => collect(preg_split('/[^a-z]+/i', strtolower($s)) ?: [])
                        ->filter(fn (string $w) => strlen($w) > 3);

                    return $words($r->check_name)->intersect($words($checkName))->isNotEmpty();
                });

            return ['gap' => $gap, 'recommendation' => $recommendation];
        })->values();
    }

    /**
     * Distinct Oweru services mapped to this business's preliminary gaps —
     * what staff should pitch when they reach out (no health scan needed).
     *
     * @param Collection|null $recommendations preloaded active recommendations
     * @return Collection<int, Recommendation>
     */
    public function mappedServices(?Collection $recommendations = null): Collection
    {
        return $this->gapsWithRecommendations($recommendations)
            ->pluck('recommendation')
            ->filter()
            ->unique('check_name')
            ->values();
    }
}
