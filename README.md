# toy-wp-routing
Provides a more modern experience for working with the WP Rewrite API

## Basic Usage
There are two intended ways to use this package -

### Rewrites
Rewrites are similar to the core rewrite API.

Rules are associated with a request method and a handler function.

If the regex and request method match, the handler is automatically invoked.

If the regex is matched but there is no rewrite registered for the request method, a 405 response is
returned instead.

Additionally, it is not ever necessary to flush rewrite rules or manually register query variables.

```php
// RewriteCollection accepts an optional prefix which is automatically prepended to query variables.
$rewrites = new \ToyWpRouting\RewriteCollection('pfx_');

$rewrites->get('^api/users/([^/]+)$', 'index.php?id=$matches[1]', function($attrs) {
  // Matched query variables will be available in the $attrs array.
  $id = (int) $attrs['id'];

  // Do something with the user ID - e.g. lookup user by ID and send JSON response.
});

// Optionally add a callback to enable/disable a given rewrite.
// Notice in this example that the '$query' param can optionally be left empty.
$rewrites->get('^api/products$', '', function() { /** */ })->setIsActiveCallback(function() {
  // This function should return true to enable this rule, false to disable it.
});

// Hook everything in to WordPress.
(new \ToyWpRouting\Orchestrator($rewrites))->initialize();
```

### Routes
Routes can be used to more closely mimic the experience of a modern router. The route syntax comes
from [nikic/fast-route](https://github.com/nikic/FastRoute).

To use the default route syntax you must install `nikic/fast-route`:

```sh
composer require nikic/fast-route
```

Alternatively you can use a custom route syntax by implementing
`\ToyWpRouting\RouteParserInterface`. Refer to the bundled `FastRouteRouteParser` implementation and
corresponding tests to understand route parser requirements.

The route parser can optionally be installed as a dev dependency if rewrites are pre-cached on a dev
environment and synced separately to production.

```php
// Route collection also accepts an optional prefix.
$routes = new \ToyWpRouting\RouteCollection('pfx_');

$routes->get('api/users/{id}', function($attrs) {
  // Matched query variables will be available in the $attrs array.
  $id = (int) $attrs['id'];

  // ...
});

// We can still add a callback to enable/disable a given route, but the method name is different.
$routes->get('api/products', function() { /** */ })->when(function() {
  // Return true to enable, false to disable.
});

$rewrites = (new \ToyWpRouting\RouteConverter())->convertCollection($routes);

(new \ToyWpRouting\Orchestrator($rewrites))->initialize();
```

## Caching
Whether you are using rewrites or routes, it is recommended to enable rewrite caching.

```php
$cache = new \ToyWpRouting\RewriteCollectionCache(__DIR__ . '/var/cache');

if ($cache->exists()) {
  $rewrites = $cache->get();
} else {
  // Use your preferred method to create and populate a RewriteCollection instance.
}

(new \ToyWpRouting\Orchestrator($rewrites))->initialize();
```

Caching is not automatic - You might want to use something like WP-CLI to manage the cache.

Use `RewriteCollectionCache->put(RewriteCollection)` to cache rewrites. Existing cache will be
overwritten.

Use `RewriteCollectionCache->delete()` to clear the rewrite cache.

If you are using closures for rewrite handlers or active callbacks, you must install `opis/closure`:

```sh
composer require opis/closure
```

The caching mechanism does not support using objects for rewrite handlers or active callbacks.
Instead, you should set a callable resolver on the invocation strategy instance:

```php
$rewrites = new \ToyWpRouting\RewriteCollection('pfx_');
$rewrites->get('^users$', '', 'usersIndex');

$orchestrator = new \ToyWpRouting\Orchestrator($rewrites);

$orchestrator->getInvocationStrategy()->setCallableResolver(function ($potentialCallable) {
  if ('usersIndex' === $potentialCallable) {
    return [new UsersController(), 'index'];
  }

  // Etc...

  return $potentialCallable;
});

$orchestrator->initialize();
```
