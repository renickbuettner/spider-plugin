<?php

/*
 * Locates the October CMS test bootstrap. Works for a regular install
 * (plugins/renick/spider) and for a symlinked plugin directory by falling
 * back to the working directory (php artisan plugin:test runs from the app root).
 */
$candidates = [
    dirname(__DIR__, 3) . '/modules/system/tests/bootstrap.php',
    getcwd() . '/modules/system/tests/bootstrap.php',
];

foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        require_once $candidate;
        return;
    }
}

throw new RuntimeException('October CMS test bootstrap not found. Run from the application root.');
