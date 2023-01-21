<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct('responders_', $invocationStrategy);

            $this->queryVariables = array (
  'responders_matchedRule' => 'matchedRule',
);
            $this->rewriteRules = array (
  '^responders/http-exception/not-found$' => 'index.php?responders_matchedRule=36cf81deb1f69287bdeb9081a63a5cac',
  '^responders/http-exception/method-not-allowed$' => 'index.php?responders_matchedRule=9df9d8c2b2f490d792e0fa9e024e0c63',
  '^responders/json$' => 'index.php?responders_matchedRule=d244e5dadff1f64ec6f284bb9c460b3f',
  '^responders/query$' => 'index.php?responders_matchedRule=08fdc3754b2d4e6a9d77537196a067cd',
  '^responders/redirect$' => 'index.php?responders_matchedRule=80a2bb3aa93b585ea0d8c1d37a6478c4',
  '^responders/template$' => 'index.php?responders_matchedRule=8e642eb23c12a00d8dabe1eb35c1c257',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'responders_matchedRule' => 'matchedRule',
), array (
  0 => 'responders_matchedRule',
), $this->invocationStrategy, static function () {
            // @todo custom additional headers
            throw new \ToyWpRouting\Exception\NotFoundHttpException();
        }, NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'responders_matchedRule' => 'matchedRule',
), array (
  0 => 'responders_matchedRule',
), $this->invocationStrategy, static function () {
            // @todo custom additional headers, custom theme template (body class and title), ensure query flags are reset
            throw new \ToyWpRouting\Exception\MethodNotAllowedHttpException(['POST', 'PUT']);
        }, NULL);
$rewrite2 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'responders_matchedRule' => 'matchedRule',
), array (
  0 => 'responders_matchedRule',
), $this->invocationStrategy, static function () {
            // @todo custom status codes, error vs success status codes, json options, non-enveloped response, custom additional headers
            return new \ToyWpRouting\Responder\JsonResponder('hello from the json responder route');
        }, NULL);
$rewrite3 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'responders_matchedRule' => 'matchedRule',
), array (
  0 => 'responders_matchedRule',
), $this->invocationStrategy, static function () {
            // @todo overwrite query variables
            add_action('twr_test_data', function () {
                global $wp;

                printf('<span class="query-responder-dump">%s</span>', json_encode($wp->query_vars));
            });

            return new \ToyWpRouting\Responder\QueryResponder(['custom-query-variable' => 'from-the-query-route']);
        }, NULL);
$rewrite4 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'responders_matchedRule' => 'matchedRule',
), array (
  0 => 'responders_matchedRule',
), $this->invocationStrategy, static function () {
            // @todo custom status code, custom redirect-by, external (unsafe) redirect both allowed and not, custom headers
            return new \ToyWpRouting\Responder\RedirectResponder('/responders/query/');
        }, NULL);
$rewrite5 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'responders_matchedRule' => 'matchedRule',
), array (
  0 => 'responders_matchedRule',
), $this->invocationStrategy, static function () {
            // @todo body class, document title, enqueue assets, dequeue assets, custom headers, query vars, query flags
            return new \ToyWpRouting\Responder\TemplateResponder('/var/www/html/wp-content/plugins/twr-test-plugin' . '/templates/hello-world.php');
        }, NULL);
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
$this->rewrites->attach($rewrite2);
$this->rewrites->attach($rewrite3);
$this->rewrites->attach($rewrite4);
$this->rewrites->attach($rewrite5);
$this->rewritesByHashAndMethod = array (
  '36cf81deb1f69287bdeb9081a63a5cac' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  '9df9d8c2b2f490d792e0fa9e024e0c63' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
  ),
  'd244e5dadff1f64ec6f284bb9c460b3f' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
  ),
  '08fdc3754b2d4e6a9d77537196a067cd' => 
  array (
    'GET' => $rewrite3,
    'HEAD' => $rewrite3,
  ),
  '80a2bb3aa93b585ea0d8c1d37a6478c4' => 
  array (
    'GET' => $rewrite4,
    'HEAD' => $rewrite4,
  ),
  '8e642eb23c12a00d8dabe1eb35c1c257' => 
  array (
    'GET' => $rewrite5,
    'HEAD' => $rewrite5,
  ),
);
        }
    };
};
