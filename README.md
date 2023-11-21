# simple-wp-routing

Syntactic sugar over the `WP_Rewrite` API so we can pretend that WordPress has a modern router.

## Warning

This package is currently in development and is subject to breaking changes without notice until v1.0 has been tagged.

It is one in a series of [WordPress toys](https://github.com/ssnepenthe?tab=repositories&q=topic%3Atoy+topic%3Awordpress&type=&language=&sort=) I have been working on with the intention of exploring ways to modernize the feel of working with WordPress.

As the label suggests, it should be treated as a toy.

## Installation

```sh
composer require ssnepenthe/simple-wp-routing
```

## Basic Usage

Intended usage is via the Router class.

### Overview

```php
use SimpleWpRouting\Exception\NotFoundHttpException;
use SimpleWpRouting\Responder\JsonResponder;
use SimpleWpRouting\Router;

// Create a router instance.
$router = new Router();

// Optional - configure your router via various available setters.
// At a minimum it is recommended to set a route prefix in order to avoid conflicts with core and other plugins.
$router->setPrefix('pfx_');

// Wire your router up with WordPress.
$router->initialize(

  // The initialize method accepts a callback which is where you should add all of your routes.
  function (Router $router) {

    // Routes are registered via HTTP method shortcuts on the router instance.
    $route = $router->get(

      // Route syntax comes from FastRoute.
      'api/users/{user}',

      // Route handlers are automatically invoked for their corresponding route/HTTP method pair.
      function (array $vars) {

        // Handlers receive an array of matched route variables by default.
        $user = getUserDataById((int) $vars['user']);

        // HTTP exceptions are automatically converted to error responses.
        if (null === $user) {
          throw new NotFoundHttpException();
        }

        // Handlers can optionally return a responder instance.
        return new JsonResponder($user);
      }
    );

    // Routes can optionally be configured with an active callback.
    $route->setIsActiveCallback(function () {

      // Return true to enable this route, false to disable it.
      return isApiUserEndpointEnabled();
    });
  }
);
```

### Route Syntax

The default route syntax comes from FastRoute.

Route variables are wrapped in curly brackets and match the regex pattern `[^/]+` by default (e.g. `users/{user}`). Custom regex patterns can be provided using a `name:pattern` syntax (e.g. `users/{user:\d+}`). Capture groups are not allowed in custom patterns.

Optional route segments are defined using square brackets (e.g. `users/{user}[/favorites]`). Nested optional segments are also supported (e.g. `users[/{user}[/favorites]]`). Optional segments are only supported at the end of route strings.

If you have a route syntax that you prefer over FastRoute, you can provide a custom route parser. Your parser must implement `\SimpleWpRouting\Parser\RouteParserInterface`. Refer to the included `tests/Unit/Parser/FastRouteRouteParserTest.php` file to understand route parser requirements.

### Route Handlers

Route handlers are automatically called within the `'parse_request'` hook when their corresponding route/method pair matches the current request.

Allowed types for handlers are defined by the callable resolver. With the default config, handlers must be a PHP callable. If using the PSR container callable resolver, handlers may also be a string identifier that resolves a callable from your container or a callable shaped array where index 0 is a string identifier that resolves an object from your container and index 1 is a callable method on that object.

The function signature is defined by the configured invoker. The default invoker provides an array containing all matched route variables keyed by variable name. The PHP-DI invoker provides matched route variables directly by name.

HTTP exceptions can be used as a convenient escape hatch from handlers.

#### NotFoundHttpException

This is currently the only non-internal HTTP exception and can be used to show a 404 page.

```php
use SimpleWpRouting\NotFoundHttpException;

$router->get('books/{book}', function (array $vars) {
  if (! $book = getBookById($vars['book'])) {
    throw new NotFoundHttpException();
  }

  // ...
});
```

Route handlers can optionally return an instance of `SimpleWpRouting\Responder\ResponderInterface`. The `respond` method on the returned responder will automatically be invoked on the WordPress `parse_request` action.

This allows common behavior to easily be wrapped up for reuse.

The following basic responder implementations are included:

#### JsonResponder

```php
use SimpleWpRouting\Responder\JsonResponder;

$router->get('api/products', function () {
  return new JsonResponder(['products' => getAllProducts()]);
});
```

Responses are sent using `wp_send_json_success` or `wp_send_json_error` depending on the status code, so data will be available at `response.data`.

#### QueryResponder

```php
use SimpleWpRouting\Responder\QueryResponder;

$router->get('products/random[/{count}]', function (array $vars) {
  $count = (int) ($vars['count'] ?? 5);

  return new QueryResponder([
    'post_type' => 'pfx_product',
    'orderby' => 'rand',
    'posts_per_page' => clamp($count, 1, 10),
  ]);
});
```

Query variables are applied on the `parse_request` hook, before the main query is run.

#### RedirectResponder

```php
use SimpleWpRouting\Responder\RedirectResponder;

$router->get('r/{redirect}', function (array $vars) {
  $location = getRedirectLocationById($vars['redirect']);

  return new RedirectResponder($location);
});
```

Redirects are sent using `wp_safe_redirect` by default. You can pass `false` as the 4th constructor argument to use `wp_redirect` instead:

```php
return new RedirectResponder($location, 302, 'WordPress', false);
```

#### TemplateResponder

```php
use SimpleWpRouting\Responder\TemplateResponder;

$router->get('thank-you', function () {
  return new TemplateResponder(__DIR__ . '/templates/thank-you.php');
});
```

Templates are loaded via the `template_include` filter.

### Active Callbacks

Allowed types for active callbacks are also defined by the configured callable resolver.

Active callbacks should return a boolean value - when true the route will be considered active, when false the route will be considered inactive.

Rewrite rules and query variables for inactive routes are still registered with WordPress, but visiting the route will result in a 404 response.

### Error Templates

Basic 400 and 405 error templates are included with styling loosely modeled after the twentytwentytwo 404 template.

These can be overridden in themes by creating `400.php` and `405.php` templates or `400.html` and `405.html` block templates.

### Likely Changes

Types of various SPL exceptions used throughout the package as well as revisiting the general hierarchy of package exceptions.

Responder internals - partials concept is a bit convoluted/over-engineered. Any changes shouldn't affect the top-level responders meant for use by end-users.
