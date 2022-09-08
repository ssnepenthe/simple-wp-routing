<?php

/**
 * Plugin Name: TWR Test Plugin
 * Plugin URI: https://github.com/ssnepenthe/toy-wp-routing
 * Description: The plugin used for testing the toy-wp-routing package.
 * Author: ssnepenthe
 * Version: 1.0.0
 */

use ToyWpRouting\Exception\MethodNotAllowedHttpException;
use ToyWpRouting\Exception\NotFoundHttpException;
use ToyWpRouting\Orchestrator;
use ToyWpRouting\Responder\JsonResponder;
use ToyWpRouting\Responder\QueryResponder;
use ToyWpRouting\Responder\RedirectResponder;
use ToyWpRouting\Responder\TemplateResponder;
use ToyWpRouting\RouteCollection;
use ToyWpRouting\RouteConverter;

require_once __DIR__ . '/../../../vendor/autoload.php';

add_action('wp_footer', function () {
    echo '<div class="twr-test-data">';

    do_action('twr_test_data');

    echo '</div>';
}, 999);

(function () {
    // Responder tests.
    $routes = new RouteCollection('responders_');

    $routes->get('responders/http-exception/not-found', function () {
        // @todo custom additional headers
        throw new NotFoundHttpException();
    });

    $routes->get('responders/http-exception/method-not-allowed', function () {
        // @todo custom additional headers, custom theme template (body class and title), ensure query flags are reset
        throw new MethodNotAllowedHttpException(['POST', 'PUT']);
    });

    $routes->get('responders/json', function () {
        // @todo custom status codes, error vs success status codes, json options, non-enveloped response, custom additional headers
        return new JsonResponder('hello from the json responder route');
    });

    $routes->get('responders/query', function () {
        // @todo overwrite query variables
        add_action('twr_test_data', function () {
            global $wp;

            printf('<span class="query-responder-dump">%s</span>', json_encode($wp->query_vars));
        });

        return new QueryResponder(['custom-query-variable' => 'from-the-query-route']);
    });

    $routes->get('responders/redirect', function () {
        // @todo custom status code, custom redirect-by, external (unsafe) redirect both allowed and not, custom headers
        return new RedirectResponder('/responders/query/');
    });

    $routes->get('responders/template', function () {
        // @todo body class, document title, enqueue assets, dequeue assets, custom headers, query vars, query flags
        return new TemplateResponder(__DIR__ . '/templates/hello-world.php');
    });

    $rewrites = (new RouteConverter())->convertCollection($routes);

    (new Orchestrator($rewrites))->initialize();
})();
