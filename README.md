# toy-wp-routing
Provides a more modern experience for working with the WP Rewrite API

## Warning
This package is currently in development and is subject to breaking changes without notice until v1.0 has been tagged.

It is one in a series of [WordPress toys](https://github.com/ssnepenthe?tab=repositories&q=topic%3Atoy+topic%3Awordpress&type=&language=&sort=) I have been working on with the intention of exploring ways to modernize the feel of working with WordPress.

As the label suggests, it should be treated as a toy.

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

Note that if you are using the same regex for multiple request methods, their query strings must all match.

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

## Responders
Rewrite handlers can optionally return an instance of `ToyWpRouting\Responder\ResponderInterface`. The `respond` method on the returned responder will automatically be invoked on the `request` filter.

This allows common behavior to easily be wrapped up for reuse.

The following basic responder implementations are included:

### ToyWpRouting\Responder\JsonResponder
```php
$routes->get('api/products', function () {
  $products = getAllProducts();

  return new JsonResponder(['products' => $products]);
});
```

Responses are sent using `wp_send_json_success` or `wp_send_json_error` depending on the status code, so data will be available at `response.data`.

### ToyWpRouting\Responder\NotFoundResponder
```php
$routes->get('api/products/{product}', function ($attrs) {
  $product = getProductById($attrs['product']);

  if (! $product) {
    return new NotFoundResponder();
  }

  return new JsonResponder($product);
});
```

### ToyWpRouting\Responder\RedirectResponder
```php
$routes->get('r/{redirect}', function ($attrs) {
  $location = getRedirectLocationById($attrs['redirect']);

  return new RedirectResponder($location);
});
```

Redirects are sent using `wp_safe_redirect` by default. You can optionally chain a call to the `withUnsafeRedirectsAllowed` method to use `wp_redirect` instead.

```php
return (new RedirectResponder($location))->withUnsafeRedirectsAllowed();
```

### ToyWpRouting\Responder\TemplateResponder
```php
$routes->get('thank-you', function () {
  return new TemplateResponder(__DIR__ . '/templates/thank-you.php');
});
```

Templates are loaded via the `template_include` filter.

## Caching
If you have opcache enabled, you may see improved performance by enabling rewrite caching.

```php
$cache = new \ToyWpRouting\RewriteCollectionCache(__DIR__ . '/var/cache');

if ($cache->exists()) {
  $rewrites = $cache->get();
} else {
  // Use your preferred method to create and populate a RewriteCollection instance.
}

(new \ToyWpRouting\Orchestrator($rewrites))->initialize();
```

Caching is not automatic - You will want to use something like WP-CLI to manage the cache.

Use `RewriteCollectionCache->put(RewriteCollection)` to cache rewrites. Existing cache will be
overwritten.

Use `RewriteCollectionCache->delete()` to clear the rewrite cache.

If you are using closures for rewrite handlers or active callbacks, you must install `opis/closure`:

```sh
composer require opis/closure
```

The caching mechanism does not support using instance methods for rewrite handlers or active callbacks.
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

Please note that if opcache is not enabled performance will likely suffer by using the rewrite cache.

That said - It is always best to perform some sort of profiling on a case-by-case base in order to determine what is best for your specific environment.
