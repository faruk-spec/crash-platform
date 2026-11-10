<?php
/**
 * Autonomous Crash Game Platform - Monorepo Entrypoint
 * Bridges web requests directly into the Laravel backend.
 */

$publicPath = __DIR__ . '/backend/public/index.php';

if (file_exists($publicPath)) {
    require_once $publicPath;
} else {
    echo "<h1>Crash Platform Monorepo</h1>";
    echo "<p>Backend files detected. Please run <code>composer install</code> inside the <code>backend/</code> folder.</p>";
}
