# toy-wp-routing
Provides a more modern routing syntax to interact with the WP Rewrite API

## Usage
```php
$routing = new ToyWpRouting\Orchestrator('/path/to/rewrite/cache/dir');

// Optionally set a prefix - this prefix is prepended to all query variables before they are registered with WP.
$routing->getContainer()->setPrefix('pfx_');

// Hook everything in to WP.
$routing->initialize();

// Optional - you might want a system in place to easily cache rewrites.
if (defined('WP_CLI') && WP_CLI) {

  WP_CLI::add_command('pfx cache-rewrites', function() use ($routing) {
    $routing->cacheRewrites();
  });

  // And maybe an easy way to clean the rewrite cache for development.
  WP_CLI::add_command('pfx clear-rewrites', function() use ($routing) {
    $container = $routing->getContainer();

    unlink("{$routing->getCacheDir()}/{$routing->getCacheFile()}");
  });

}

// Add your routes - This action only fires if rewrites have not been cached.
add_action('toy_wp_routing.init', function(ToyWpRouting\RouteCollection $routes) {

  // Route syntax comes from nikic/fast-route.
  $routes->get('api/users/{id}', function($id) {
    // This function is automatically invoked from the 'request' filter when this route is matched.
    // That is, on a GET request to a path that matches the regex '^users/([^/]+)$'.
  });

  // Optionally add a callback to enable/disable a given route.
  $routes->get('api/products', function() { /** */ })->when(function() {
    // Return true to enable, false to disable. It is never necessary to flush rewrites.
  });

});
```