# toy-wp-routing
Provides a more modern routing syntax to interact with the WP Rewrite API

## Basic Usage
```php
$routing = new \ToyWpRouting\Orchestrator();

// Optional but recommended - set a prefix.
// This prefix is prepended to all query variables before they are registered with WP.
$routing->getContainer()->setPrefix('pfx_');

// Hook everything in to WP.
$routing->initialize();

// Add your routes using the 'toy_wp_routing.init' hook.
add_action('toy_wp_routing.init', function(\ToyWpRouting\RouteCollection $routes) {
  // Route syntax comes from nikic/fast-route.
  $routes->get('api/users/{id}', function($id) {
    // This function is automatically invoked from the 'request' filter when this route is matched.
    // That is, on a GET request to a path that matches the regex '^api/users/([^/]+)$'.
  });

  // Optionally add a callback to enable/disable a given route.
  $routes->get('api/products', function() { /** */ })->when(function() {
    // Return true to enable, false to disable. It is never necessary to flush rewrites.
  });
});
```

## Enable Rewrite Cache
```php
$routing = new \ToyWpRouting\Orchestrator();

// Set cache dir.
$routing->getContainer()->setCacheDir(__DIR__ . '/var/cache');

$routing->getContainer()->setPrefix('pfx_');
$routing->initialize();

// This action will not fire when rewrites are cached.
add_action('toy_wp_routing.init', function(\ToyWpRouting\RouteCollection $routes) {
  //
});

// Caching is not automatic - Let's use WP-CLI to manage the cache.
if (defined('WP_CLI') && WP_CLI) {
  WP_CLI::add_command('pfx cache-rewrites', function() use ($routing) {
    $routing->cacheRewrites();
  });

  WP_CLI::add_command('pfx clear-rewrites', function() use ($routing) {
    $container = $routing->getContainer()->getRewriteCollectionCache()->delete();
  });
}
```
