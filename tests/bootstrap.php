<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Support/IsolatedTestModules.php';

// Detect ParaTest token or use PID
$token = getenv('TEST_TOKEN') ?: getmypid();
$cachePath = sys_get_temp_dir() . '/modularous_cache_' . $token;

if (! is_dir($cachePath)) {
    mkdir($cachePath, 0777, true);
}

// Redirect all Laravel manifest files to the unique temp directory
putenv("APP_SERVICES_CACHE={$cachePath}/services.php");
putenv("APP_PACKAGES_CACHE={$cachePath}/packages.php");
putenv("APP_CONFIG_CACHE={$cachePath}/config.php");
putenv("APP_ROUTES_CACHE={$cachePath}/routes.php");
putenv("APP_EVENTS_CACHE={$cachePath}/events.php");

// Also set a constant for internal use if needed
if (! defined('MODULAROUS_TEST_TOKEN')) {
    define('MODULAROUS_TEST_TOKEN', $token);
}
if (! defined('MODULAROUS_PROCESS_CACHE_PATH')) {
    define('MODULAROUS_PROCESS_CACHE_PATH', $cachePath);
}
