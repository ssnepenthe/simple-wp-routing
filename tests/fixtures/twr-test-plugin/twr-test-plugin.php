<?php

/**
 * Plugin Name: TWR Test Plugin
 * Plugin URI: https://github.com/ssnepenthe/toy-wp-routing
 * Description: The plugin used for testing the toy-wp-routing package.
 * Author: ssnepenthe
 * Version: 1.0.0
 */

namespace TwrTestPlugin;

if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
    return;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/test-groups.php';

add_action('wp_footer', function () {
    global $wp;

    echo '<div class="twr-test-data">';

    printf('<span class="twr-rewrites">%s</span>', json_encode(get_option('rewrite_rules')));
    printf('<span class="twr-query-vars">%s</span>', json_encode($wp->public_query_vars));

    do_action('twr_test_data');

    echo '</div>';
}, 999);

foreach (TestGroup::createTestGroups() as $testGroup) {
    $testGroup->initialize();
}
