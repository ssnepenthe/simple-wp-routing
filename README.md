# toy-wp-routing

Syntactic sugar over `WP_Rewrite` so we can pretend that WordPress has a modern router.

## Warning

This package is currently in development and is subject to breaking changes without notice until v1.0 has been tagged.

It is one in a series of [WordPress toys](https://github.com/ssnepenthe?tab=repositories&q=topic%3Atoy+topic%3Awordpress&type=&language=&sort=) I have been working on with the intention of exploring ways to modernize the feel of working with WordPress.

As the label suggests, it should be treated as a toy.

## Basic Usage

Intended usage is via the Router class.

### Overview

```php
use ToyWpRouting\Exception\NotFoundHttpException;
use ToyWpRouting\Responder\JsonResponder;
use ToyWpRouting\Router;

// Create a router instance.
$router = new Router();

// Optional - configure your router.
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

        // Handlers receive an array of matched query variables by default.
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
      // When disabled, the rewrite rules for this route are still registered but visiting the route will result in a 404.
      return isApiUserEndpointEnabled();
    });
  }
);
```

### Responders

Route handlers can optionally return an instance of `ToyWpRouting\Responder\ResponderInterface`. The `respond` method on the returned responder will automatically be invoked on the WordPress `parse_request` action.

This allows common behavior to easily be wrapped up for reuse.

The following basic responder implementations are included:

#### ToyWpRouting\Responder\JsonResponder

```php
$router->get('api/products', function () {
  return new JsonResponder(['products' => getAllProducts()]);
});
```

Responses are sent using `wp_send_json_success` or `wp_send_json_error` depending on the status code, so data will be available at `response.data`.

#### ToyWpRouting\Responder\QueryResponder

```php
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

#### ToyWpRouting\Responder\RedirectResponder

```php
$router->get('r/{redirect}', function (array $vars) {
  $location = getRedirectLocationById($vars['redirect']);

  return new RedirectResponder($location);
});
```

Redirects are sent using `wp_safe_redirect` by default. You can pass `false` as the 4th constructor argument to use `wp_redirect` instead:

```php
return new RedirectResponder($location, 302, 'WordPress', false);
```

#### ToyWpRouting\Responder\TemplateResponder

```php
$router->get('thank-you', function () {
  return new TemplateResponder(__DIR__ . '/templates/thank-you.php');
});
```

Templates are loaded via the `template_include` filter.

### HTTP exceptions

HTTP exceptions can be used as a convenient escape hatch from handlers.

#### ToyWpRouting\Exception\NotFoundHttpException

This is currently the only non-internal HTTP exception and can be used to show a 404 page.

```php
$router->get('books/{book}', function (array $vars) {
  if (! $book = getBookById($vars['book'])) {
    throw new NotFoundHttpException();
  }

  // ...
});
```

### Templates

Basic 400 and 405 error templates are included with styling loosely modeled after the twentytwentytwo 404 template.

These can be overridden in themes by creating `400.php` and `405.php` templates or `400.html` and `405.html` block templates.
