<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\ScrapedBusiness;
use App\Models\ScrapeWatchEvent;
use Illuminate\Support\Facades\Log;

/**
 * Watchdog for the 24/7 scraper: compares every scheduled re-scrape against
 * the previous snapshot of a business and records/reports meaningful events.
 *
 * Watched signals:
 *  - rating drops (fresh outreach hook: "their reputation is slipping")
 *  - new reviews (hot when negative)
 *  - contact / services changes (stale data guard)
 *  - site down / back online (availability watchdog)
 *
 * "Hot" events alert staff via NotificationService, with a per-business
 * cooldown so a flapping site or slow decline cannot spam email daily.
 */
class ScrapeWatchdogService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    /**
     * Evaluate one completed scrape against the previous state.
     *
     * @param array|null $before the business state as it was BEFORE this
     *                      scrape's fields were written (snapshot built by
     *                      ScraperClient) — null when the business is new
     * @param array $fresh  the newly extracted payload (business_name, email,
     *                      phone, address, services, rating_avg, rating_count,
     *                      reviews) — same shape as the scraper's response
     */
    public function inspect(ScrapedBusiness $business, ?array $before, array $fresh): void
    {
        if (! config('owers.scraper.watchdog.enabled', true)) {
            return;
        }

        // First successful scrape of this target: establish the baseline only.
        if ($before === null || empty($before['has_scraped'])) {
            $this->storeSeenReviews($business, $fresh['reviews'] ?? []);

            return;
        }

        $events = collect()
            ->merge($this->detectRatingDrop($before, $fresh))
            ->merge($this->detectNewReviews($business, $before, $fresh))
            ->merge($this->detectFieldChanges($before, $fresh));

        foreach ($events as $event) {
            $this->record($business, $event);
        }

        // A success after a recorded outage closes the loop.
        if ($this->hasOpenSiteDown($business)) {
            $this->record($business, [
                'type' => ScrapeWatchEvent::TYPE_BACK_ONLINE,
                'severity' => ScrapeWatchEvent::SEVERITY_INFO,
                'changes' => ['message' => 'Website responded again after a previous failure.'],
            ]);
        }
    }

    /**
     * A scrape attempt failed. If the business was previously scraped
     * successfully, watch it: record site_down and alert staff on the first
     * failure (cooldown-guarded), because an offline site is both an outage
     * signal and an outreach hook ("your site is down — we can host it").
     */
    public function inspectFailure(ScrapedBusiness $business): void
    {
        if (! config('owers.scraper.watchdog.enabled', true)) {
            return;
        }

        if ((int) $business->scrape_count === 0) {
            return; // never scraped successfully — nothing watched yet
        }

        $this->record($business, [
            'type' => ScrapeWatchEvent::TYPE_SITE_DOWN,
            'severity' => ScrapeWatchEvent::SEVERITY_HOT,
            'changes' => ['message' => 'Website could not be reached or returned an error during scheduled re-scrape.'],
        ]);
    }

    // ------------------------------------------------------------------
    // Detectors
    // ------------------------------------------------------------------

    protected function detectRatingDrop(array $before, array $fresh): array
    {
        $old = $before['rating_avg'] ?? null;
        $new = $fresh['rating_avg'] ?? null;

        if ($old === null || $new === null || (float) $new >= (float) $old) {
            return [];
        }

        $threshold = (float) config('owers.scraper.watchdog.rating_drop_threshold', 0.5);

        if ((float) $old - (float) $new < $threshold) {
            return [];
        }

        return [[
            'type' => ScrapeWatchEvent::TYPE_RATING_DROP,
            'severity' => ScrapeWatchEvent::SEVERITY_HOT,
            'changes' => [
                'rating_avg' => ['from' => (float) $old, 'to' => (float) $new],
                'rating_count' => ['from' => $before['rating_count'] ?? null, 'to' => $fresh['rating_count'] ?? null],
            ],
        ]];
    }

    protected function detectNewReviews(ScrapedBusiness $business, array $before, array $fresh): array
    {
        $reviews = $fresh['reviews'] ?? [];
        if (! is_array($reviews) || $reviews === []) {
            return [];
        }

        $seen = $before['seen_review_texts'] ?? [];
        if (! is_array($seen)) {
            $seen = [];
        }

        $known = collect($seen)
            ->map(fn ($text) => $this->reviewKey($text))
            ->filter()
            ->all();

        $badThreshold = (float) config('owers.scraper.watchdog.bad_review_threshold', 3);

        $events = [];
        $newKeys = [];

        foreach ($reviews as $review) {
            $text = is_array($review) ? (string) ($review['text'] ?? '') : (string) $review;
            $key = $this->reviewKey($text);

            if ($key === '' || in_array($key, $known, true)) {
                continue;
            }

            $newKeys[] = $key;

            $rating = is_array($review) ? ($review['rating'] ?? null) : null;
            $author = is_array($review) ? ($review['author'] ?? '') : '';

            $events[] = [
                'type' => ScrapeWatchEvent::TYPE_NEW_REVIEW,
                'severity' => ($rating !== null && (float) $rating <= $badThreshold)
                    ? ScrapeWatchEvent::SEVERITY_HOT
                    : ScrapeWatchEvent::SEVERITY_NOTEWORTHY,
                'changes' => [
                    'author' => $author,
                    'rating' => $rating,
                    'text' => str($text)->limit(200)->toString(),
                ],
            ];
        }

        if ($newKeys !== []) {
            // Remember what we have already reported (cap so the JSON stays small).
            $this->storeSeenReviews($business, array_merge(
                $seen,
                array_slice($reviews, 0, count($newKeys)),
            ));
        }

        return $events;
    }

    protected function detectFieldChanges(array $before, array $fresh): array
    {
        $watched = [
            'email' => ScrapeWatchEvent::TYPE_CONTACT_CHANGED,
            'phone' => ScrapeWatchEvent::TYPE_CONTACT_CHANGED,
            'address' => ScrapeWatchEvent::TYPE_CONTACT_CHANGED,
            'services' => ScrapeWatchEvent::TYPE_SERVICES_CHANGED,
        ];

        $changes = [];

        foreach ($watched as $field => $type) {
            $old = trim((string) ($before[$field] ?? ''));
            $new = trim((string) ($fresh[$field] ?? ''));

            // Only a real change between two known values counts; a field that
            // was empty before (or became empty) is noise, not news.
            if ($old === '' || $new === '' || strcasecmp($old, $new) === 0) {
                continue;
            }

            $changes[] = [
                'type' => $type,
                'severity' => ScrapeWatchEvent::SEVERITY_NOTEWORTHY,
                'changes' => ['field' => $field, 'from' => str($old)->limit(120)->toString(), 'to' => str($new)->limit(120)->toString()],
            ];
        }

        return $changes;
    }

    // ------------------------------------------------------------------
    // Recording + alerting
    // ------------------------------------------------------------------

    protected function record(ScrapedBusiness $business, array $event): ScrapeWatchEvent
    {
        $record = ScrapeWatchEvent::create([
            'scraped_business_id' => $business->id,
            'type' => $event['type'],
            'severity' => $event['severity'],
            'changes' => $event['changes'] ?? null,
        ]);

        if ($record->isHot()) {
            $this->maybeNotify($business, $record);
        }

        return $record;
    }

    protected function maybeNotify(ScrapedBusiness $business, ScrapeWatchEvent $event): void
    {
        $cooldown = (int) config('owers.scraper.watchdog.notify_cooldown_hours', 24);

        // Cooldown: at most one watchdog alert per business per window, so a
        // flapping site or sliding rating cannot flood the inbox.
        $recentlyNotified = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->whereNotNull('notified_at')
            ->where('notified_at', '>=', now()->subHours(max(1, $cooldown)))
            ->exists();

        if ($recentlyNotified) {
            return;
        }

        [$subject, $message] = $this->buildAlert($business, $event);

        try {
            $this->notifications->alertStaff($subject, $message, $business);
            $event->update(['notified_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Watchdog staff alert failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function buildAlert(ScrapedBusiness $business, ScrapeWatchEvent $event): array
    {
        $name = $business->business_name ?: $business->website_url;

        return match ($event->type) {
            ScrapeWatchEvent::TYPE_RATING_DROP => [
                "Watchdog: rating dropped for {$name}",
                sprintf(
                    "The public rating of %s fell from %s to %s (%d reviews).\n\nWebsite: %s\n\nA slipping reputation is a strong outreach moment — review their page and consider a phone call.",
                    $name,
                    $event->changes['rating_avg']['from'] ?? '?',
                    $event->changes['rating_avg']['to'] ?? '?',
                    (int) ($event->changes['rating_count']['to'] ?? 0),
                    $business->website_url,
                ),
            ],
            ScrapeWatchEvent::TYPE_NEW_REVIEW => [
                sprintf("Watchdog: new %s review for %s", ($event->changes['rating'] ?? null) !== null ? (string) $event->changes['rating'] . '★' : '', $name),
                sprintf(
                    "%s received a new public review%s:\n\n\"%s\"\n\nWebsite: %s\n\n%s",
                    $name,
                    isset($event->changes['rating']) ? ' rated ' . $event->changes['rating'] . '/5' : '',
                    $event->changes['text'] ?? '',
                    $business->website_url,
                    ((float) ($event->changes['rating'] ?? 5)) <= (float) config('owers.scraper.watchdog.bad_review_threshold', 3)
                        ? 'This is a negative review — a good excuse to reach out with a fix.'
                        : 'A fresh review is a natural touchpoint for outreach.',
                ),
            ],
            ScrapeWatchEvent::TYPE_SITE_DOWN => [
                "Watchdog: website DOWN — {$name}",
                sprintf(
                    "The website of %s could not be reached during the scheduled re-scrape.\n\nURL: %s\n\nAn offline site is lost business for them and an opening for you: hosting/care-plan pitch.\n\n(This alert repeats at most once every %d hours while the site stays down.)",
                    $name,
                    $business->website_url,
                    (int) config('owers.scraper.watchdog.notify_cooldown_hours', 24),
                ),
            ],
            default => [
                "Watchdog: {$event->type} — {$name}",
                "{$event->type} detected on {$business->website_url}.",
            ],
        };
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function hasOpenSiteDown(ScrapedBusiness $business): bool
    {
        $lastDown = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_SITE_DOWN)
            ->latest('id')
            ->first();

        if (! $lastDown) {
            return false;
        }

        // Open unless a back_online event already followed it.
        return ! ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_BACK_ONLINE)
            ->where('created_at', '>=', $lastDown->created_at)
            ->exists();
    }

    protected function reviewKey(string $text): string
    {
        $normalized = mb_strtolower(preg_replace('/\s+/', ' ', trim($text)) ?? '');

        return mb_substr($normalized, 0, 120);
    }

    protected function storeSeenReviews(ScrapedBusiness $business, array $reviews): void
    {
        $existing = collect($business->seen_review_texts ?? [])->all();

        foreach ($reviews as $review) {
            $text = is_array($review) ? (string) ($review['text'] ?? '') : (string) $review;
            $key = $this->reviewKey($text);

            if ($key !== '' && ! in_array($key, $existing, true)) {
                $existing[] = $key;
            }
        }

        $business->forceFill(['seen_review_texts' => array_slice($existing, -50)])->save();
    }
}
