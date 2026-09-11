<?php

require __DIR__.'/../vendor/autoload.php';

/*
 * Windows system environment variables can pin APP_ENV (e.g. "local"), which
 * leaks into $_SERVER. phpdotenv treats those variables as already set and
 * refuses to overwrite them, so PHPUnit's <env> settings alone can't switch
 * the app to the "testing" environment (breaking CSRF bypass, mail array
 * driver, etc.). Force the testing environment here, before Laravel boots.
 */
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');
