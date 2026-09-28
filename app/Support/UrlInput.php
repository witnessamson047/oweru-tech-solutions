<?php

namespace App\Support;

use App\Models\ScrapeTarget;

/**
 * Forgiving URL input for humans.
 *
 * Non-technical users type "modewjifoundation.org", "www.abc.co.tz/",
 * "\"abc.co.tz\"" (with copy-paste quotes) or forget "https://" entirely.
 * Laravel's `url` rule and the browser's type="url" input reject all of
 * those, which made every entry point feel broken. Every URL entry point
 * (public scanner, scraper form, website form, bulk targets) goes through
 * this helper so ANY of these forms works everywhere.
 */
class UrlInput
{
    /**
     * Normalize messy human input into a clean http(s) URL, or null when the
     * input cannot possibly be a public website (so we can show one friendly,
     * consistent error message instead of a validator failure).
     */
    public static function normalize(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        // Strip copy-paste wrapping: quotes, angle brackets, trailing
        // punctuation a user might have picked up from a document or message.
        $raw = preg_replace('/^[\s"\'<]+|[\s"\'<>\,\.\x{3002}\x{FF0C}]+$/u', '', $raw) ?? '';

        if ($raw === '' || str_contains($raw, ' ')) {
            return null;
        }

        // Core validation + scheme completion + non-business host filtering
        // (social media, asset hosts) is shared with the auto-scraper queue.
        return ScrapeTarget::normalizeUrl($raw);
    }

    /**
     * Plain-language error shown when normalize() returns null.
     */
    public static function friendlyError(): string
    {
        return 'That does not look like a website address. Try something like "abc.co.tz" or "www.abc.co.tz" — you do not need to type https://';
    }
}
