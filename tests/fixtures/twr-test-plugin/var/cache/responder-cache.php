<?php

declare(strict_types=1);

return function (): \ToyWpRouting\RewriteCollection {
    return new class() extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct()
        {
            $this->queryVariables = array (
  'responders___routeType' => '__routeType',
);
            $this->rewriteRules = array (
  '^(?|responders/http-exception/not-found)$' => 'index.php?responders___routeType=static',
  '^(?|responders/http-exception/method-not-allowed)$' => 'index.php?responders___routeType=static',
  '^(?|responders/json)$' => 'index.php?responders___routeType=static',
  '^(?|responders/query)$' => 'index.php?responders___routeType=static',
  '^(?|responders/redirect)$' => 'index.php?responders___routeType=static',
  '^(?|responders/template)$' => 'index.php?responders___routeType=static',
);

            $rewrite0 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/http-exception/not-found)$', 'index.php?responders___routeType=static', array (
  'responders___routeType' => '__routeType',
), static function () {
            // @todo custom additional headers
            throw new \ToyWpRouting\Exception\NotFoundHttpException();
        }, NULL);
$rewrite1 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/http-exception/method-not-allowed)$', 'index.php?responders___routeType=static', array (
  'responders___routeType' => '__routeType',
), static function () {
            // @todo custom additional headers, custom theme template (body class and title), ensure query flags are reset
            throw new \ToyWpRouting\Exception\MethodNotAllowedHttpException(['POST', 'PUT']);
        }, NULL);
$rewrite2 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/json)$', 'index.php?responders___routeType=static', array (
  'responders___routeType' => '__routeType',
), static function () {
            // @todo custom status codes, error vs success status codes, json options, non-enveloped response, custom additional headers
            return new \ToyWpRouting\Responder\JsonResponder('hello from the json responder route');
        }, NULL);
$rewrite3 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/query)$', 'index.php?responders___routeType=static', array (
  'responders___routeType' => '__routeType',
), static function () {
            // @todo overwrite query variables
            add_action('twr_test_data', function () {
                global $wp;

                printf('<span class="query-responder-dump">%s</span>', json_encode($wp->query_vars));
            });

            return new \ToyWpRouting\Responder\QueryResponder(['custom-query-variable' => 'from-the-query-route']);
        }, NULL);
$rewrite4 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/redirect)$', 'index.php?responders___routeType=static', array (
  'responders___routeType' => '__routeType',
), static function () {
            // @todo custom status code, custom redirect-by, external (unsafe) redirect both allowed and not, custom headers
            return new \ToyWpRouting\Responder\RedirectResponder('/responders/query/');
        }, NULL);
$rewrite5 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/template)$', 'index.php?responders___routeType=static', array (
  'responders___routeType' => '__routeType',
), static function () {
            // @todo body class, document title, enqueue assets, dequeue assets, custom headers, query vars, query flags
            return new \ToyWpRouting\Responder\TemplateResponder('/var/www/html/wp-content/plugins/toy-wp-routing/tests/fixtures/twr-test-plugin' . '/templates/hello-world.php');
        }, NULL);
$this->rewritesByRegexAndMethod = array (
  '^(?|responders/http-exception/not-found)$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  '^(?|responders/http-exception/method-not-allowed)$' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
  ),
  '^(?|responders/json)$' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
  ),
  '^(?|responders/query)$' => 
  array (
    'GET' => $rewrite3,
    'HEAD' => $rewrite3,
  ),
  '^(?|responders/redirect)$' => 
  array (
    'GET' => $rewrite4,
    'HEAD' => $rewrite4,
  ),
  '^(?|responders/template)$' => 
  array (
    'GET' => $rewrite5,
    'HEAD' => $rewrite5,
  ),
);
        }

        public function getRewrites(): array
        {
            throw new LogicException('Rewrites list not accessible on cache rewrite collection');
        }
    };
};
