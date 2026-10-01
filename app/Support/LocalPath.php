<?php

namespace App\Support;

/**
 * Resolves writable directories for generated artifacts (PDF reports, receipts).
 *
 * Locally (and on any normal server) everything lives in storage/app as
 * always. On serverless platforms such as Vercel the deployment filesystem is
 * READ-ONLY — the only writable location is the system temp directory
 * (/tmp). Generated PDFs there are ephemeral, which is acceptable: the e-mail
 * with the attachment is the durable delivery channel and the Report/Invoice
 * rows keep the record.
 *
 * Override with OWERU_STORAGE_DIR when a persistent disk exists (e.g. a
 * mounted volume), otherwise storage/app is used unchanged.
 */
class LocalPath
{
    /**
     * Directory where generated scan-report PDFs are written.
     */
    public static function reportsDir(): string
    {
        return self::base('reports');
    }

    /**
     * Directory where generated invoice receipt PDFs are written.
     */
    public static function receiptsDir(): string
    {
        return self::base('receipts');
    }

    private static function base(string $subdir): string
    {
        $base = config('owers.storage.dir');

        // Default (null) = classic Laravel storage path, unchanged behaviour.
        if ($base === null || $base === '') {
            return storage_path("app/{$subdir}");
        }

        $dir = rtrim($base, '/\\') . '/' . $subdir;

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }
}
