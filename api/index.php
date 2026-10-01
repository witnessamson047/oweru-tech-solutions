<?php

/**
 * Vercel serverless entrypoint — Oweru Tech Solutions.
 *
 * vercel.json routes every non-static request here; static assets are served
 * straight from public/build and public/images by @vercel/static. This file
 * applies serverless-safe defaults (read-only filesystem!) and then hands the
 * request to the normal Laravel public/index.php.
 */

declare(strict_types=1);

$writable = sys_get_temp_dir(); // /tmp is the only writable path on Vercel

/*
 * Serverless-safe framework defaults. Values already set in the Vercel
 * dashboard are respected — these only fill the gaps so a fresh deploy
 * boots even before every env var has been configured.
 */
$defaults = [
    // Blade cannot compile into the read-only deployment; use /tmp.
    'VIEW_COMPILED_PATH' => "{$writable}/views",
    // Logs must go to stderr (visible in the Vercel runtime logs).
    'LOG_CHANNEL' => 'stderr',
    // No filesystem sessions/cache on serverless.
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    // No long-running worker on serverless: jobs run inline.
    'QUEUE_CONNECTION' => 'sync',
];

foreach ($defaults as $key => $value) {
    $current = getenv($key);
    if ($current === false || $current === '') {
        putenv("{$key}={$value}");
    }
}

// Warm the writable directories once per cold start.
foreach (["{$writable}/views", "{$writable}/reports", "{$writable}/receipts"] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

require __DIR__ . '/../public/index.php';
