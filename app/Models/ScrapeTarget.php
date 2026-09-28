<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrapeTarget extends Model
{
    public const STATUS_PAUSED = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_DONE = 2;

    public const STATUS_LABELS = [
        self::STATUS_PAUSED => 'paused',
        self::STATUS_ACTIVE => 'active',
        self::STATUS_DONE => 'done',
    ];

    /**
     * Social platforms, portals and asset hosts that are never usable as
     * scrape targets (mirror of the Python engine's NON_BUSINESS_DOMAINS).
     * Matched as full domains: host equals the entry or is a subdomain of it,
     * so real domains like market.co.tz or box.com are never caught.
     */
    public const NON_BUSINESS_DOMAINS = [
        'facebook.com', 'fb.com', 'instagram.com', 'twitter.com', 'x.com',
        'linkedin.com', 'youtube.com', 'youtu.be', 'tiktok.com', 'whatsapp.com',
        'wa.me', 'telegram.org', 't.me', 'pinterest.com', 'reddit.com',
        'snapchat.com', 'vimeo.com', 'flickr.com', 'medium.com', 'apple.com',
        'microsoft.com', 'yahoo.com', 'bing.com', 'amazon.com', 'wikipedia.org',
        'w3.org', 'schema.org', 'cloudflare.com', 'gravatar.com', 'jsdelivr.net',
        'cdnjs.cloudflare.com', 'bit.ly', 'gmail.com', 'hotmail.com',
        'outlook.com', 'canva.com', 'mailchimp.com', 'google.com',
        'googleapis.com', 'gstatic.com', 'goo.gl', 'googletagmanager.com',
        'google-analytics.com', 'doubleclick.net', 'wp.com', 'wixstatic.com',
        'github.com', 'gitlab.com', 'amazonaws.com', 'azurewebsites.net',
        'herokuapp.com', 'netlify.app', 'vercel.app', 'pages.dev', 'web.app',
        'firebaseapp.com',
    ];

    protected $fillable = [
        'url', 'status', 'last_scraped_at', 'scrape_count', 'last_error', 'notes',
        'discovery_run_id', // provenance: which OSM discovery run queued this target (null = manual/directory)
    ];

    protected $casts = [
        'last_scraped_at' => 'datetime',
        'status' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(ScrapedBusiness::class, 'url', 'website_url');
    }

    /**
     * Active targets due for (re)scraping: never scraped, past the refresh
     * interval since the last success, or failed and due for a retry.
     */
    public function scopeDue($query, int $days = 7)
    {
        $retryHours = (int) config('owers.scraper.retry_hours', 6);

        return $query->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) use ($days, $retryHours) {
                $q->whereNull('last_scraped_at')
                  ->orWhere('last_scraped_at', '<=', now()->subDays($days))
                  ->orWhere(function ($failed) use ($retryHours) {
                      // Last attempt errored → retry after retry_hours (not days)
                      $failed->whereNotNull('last_error')
                          ->where('last_scraped_at', '<=', now()->subHours(max(1, $retryHours)));
                  });
            });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? 'unknown';
    }

    /**
     * Identity key of a URL for duplicate checks: lowercased host with the
     * leading "www." stripped. abc.co.tz and www.abc.co.tz are the SAME site
     * (one scrape), while abc.co.tz and abc.com are DIFFERENT businesses
     * (the TLD is part of the identity — both are scraped).
     */
    public static function hostKey(?string $url): ?string
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));

        if ($host === '') {
            return null;
        }

        return preg_replace('/^www\./', '', $host);
    }

    /**
     * Normalize a raw input string into a valid public http(s) URL,
     * or null when it is not usable as a scrape target.
     */
    public static function normalizeUrl(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '' || str_contains($raw, ' ')) {
            return null;
        }

        if (! preg_match('#^https?://#i', $raw)) {
            $raw = 'https://' . $raw;
        }

        if (! filter_var($raw, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower((string) parse_url($raw, PHP_URL_HOST));

        // Must look like a public domain: a dot, no scheme-less junk
        if (! $host || ! str_contains($host, '.') || ! str_contains($host, '.')) {
            return null;
        }

        if (! preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $host)) {
            return null;
        }

        // Canonical form: a bare-root URL always keeps the trailing slash
        // (https://abc.co.tz → https://abc.co.tz/). Without this, the same
        // site entered with and without the slash creates duplicate records.
        if (! str_starts_with(parse_url($raw, PHP_URL_PATH) ?? '', '/')) {
            $raw = rtrim($raw, '/') . '/';
        }

        // Social platforms / asset hosts are never business targets
        foreach (self::NON_BUSINESS_DOMAINS as $bad) {
            if ($host === $bad || str_ends_with($host, '.' . $bad)) {
                return null;
            }
        }

        return self::canonicalizeUrl($raw);
    }

    /**
     * Canonical stored form of an already-valid URL: https scheme, lowercase
     * host, no "www." prefix, bare root keeps its trailing slash. One site =
     * one record, no matter how it was typed.
     */
    public static function canonicalizeUrl(string $url): string
    {
        // Always https: http://abc.co.tz and https://abc.co.tz are the same
        // site for our purposes, and the Python engine follows redirects.
        $scheme = 'https';
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);
        $port = parse_url($url, PHP_URL_PORT);
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '/');
        $query = parse_url($url, PHP_URL_QUERY);

        return $scheme . '://' . $host . ($port ? ":{$port}" : '') . $path . ($query ? "?{$query}" : '');
    }
}
