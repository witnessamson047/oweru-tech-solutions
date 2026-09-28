<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /*
     * Every test runs against an isolated, freshly migrated AND seeded
     * database (phpunit.xml.dist pins DB_DATABASE=:memory:). Before this
     * change, tests silently ran against the shared dev SQLite file —
     * polluting real data (junk scrape targets) and passing on accidental
     * state that happened to exist in the dev database.
     */
    use RefreshDatabase;

    protected bool $seed = true;
}
