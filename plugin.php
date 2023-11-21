<?php

declare(strict_types=1);

/**
 * Plugin Name: Simple WP Routing Test Plugin
 * Plugin URI: https://github.com/ssnepenthe/simple-wp-routing
 * Description: This plugin is only meant for testing the simple-wp-routing package. Under normal circumstances, this plugin should never appear in your plugin list.
 * Author: ssnepenthe
 * Version: 1.0.0
 */

if (! (defined('SWR_DEV') && SWR_DEV)) {
    // @todo deactivate and notify?
    return;
}

$plugin = __DIR__ . '/tests/fixtures/swr-test-plugin/swr-test-plugin.php';
$autoloader = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloader) && file_exists($plugin)) {
    require_once $autoloader;
    require_once $plugin;
}
