<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('CachedRewriteCollectiona045019711d09062a8f178f5b9166d750f284ad589711bfdcb47f7bc1ee6d5a0')) {
    class CachedRewriteCollectiona045019711d09062a8f178f5b9166d750f284ad589711bfdcb47f7bc1ee6d5a0 extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = array (
);
            $this->rewriteRules = array (
  '^(?|responders/http-exception/not-found)$' => 'index.php?responders___routeType=static',
  '^(?|responders/http-exception/method-not-allowed)$' => 'index.php?responders___routeType=static',
  '^(?|responders/json)$' => 'index.php?responders___routeType=static',
  '^(?|responders/query)$' => 'index.php?responders___routeType=static',
  '^(?|responders/redirect)$' => 'index.php?responders___routeType=static',
  '^(?|responders/template)$' => 'index.php?responders___routeType=static',
);

            $rewrite0 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/http-exception/not-found)$', 'index.php?responders___routeType=static', array (
), static function () {
            // @todo custom additional headers
            throw new \SimpleWpRouting\Exception\NotFoundHttpException();
        }, NULL);
$rewrite1 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/http-exception/method-not-allowed)$', 'index.php?responders___routeType=static', array (
), static function () {
            // @todo custom additional headers, custom theme template (body class and title), ensure query flags are reset
            throw new \SimpleWpRouting\Exception\MethodNotAllowedHttpException(['POST', 'PUT']);
        }, NULL);
$rewrite2 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/json)$', 'index.php?responders___routeType=static', array (
), static function () {
            // @todo custom status codes, error vs success status codes, json options, non-enveloped response, custom additional headers
            return new \SimpleWpRouting\Responder\JsonResponder('hello from the json responder route');
        }, NULL);
$rewrite3 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/query)$', 'index.php?responders___routeType=static', array (
), static function () {
            // @todo overwrite query variables
            add_action('twr_test_data', function () {
                global $wp;

                printf('<span class="query-responder-dump">%s</span>', json_encode($wp->query_vars));
            });

            return new \SimpleWpRouting\Responder\QueryResponder(['custom-query-variable' => 'from-the-query-route']);
        }, NULL);
$rewrite4 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/redirect)$', 'index.php?responders___routeType=static', array (
), static function () {
            // @todo custom status code, custom redirect-by, external (unsafe) redirect both allowed and not, custom headers
            return new \SimpleWpRouting\Responder\RedirectResponder('/responders/query/');
        }, NULL);
$rewrite5 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|responders/template)$', 'index.php?responders___routeType=static', array (
), static function () {
            // @todo body class, document title, enqueue assets, dequeue assets, custom headers, query vars, query flags
            return new \SimpleWpRouting\Responder\TemplateResponder('/var/www/html/wp-content/plugins/toy-wp-routing/tests/fixtures/twr-test-plugin' . '/templates/hello-world.php');
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
    }
}

return new CachedRewriteCollectiona045019711d09062a8f178f5b9166d750f284ad589711bfdcb47f7bc1ee6d5a0();
