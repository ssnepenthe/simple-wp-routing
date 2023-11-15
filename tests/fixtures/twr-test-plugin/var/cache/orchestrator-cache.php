<?php

declare(strict_types=1);

use ToyWpRouting\Dumper\OptimizedRewrite;
use ToyWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('CachedRewriteCollection3c95e90111a6fb2a7b1c9af68547483e1cb40c675e7d0f8de48ff88b8f982949')) {
    class CachedRewriteCollection3c95e90111a6fb2a7b1c9af68547483e1cb40c675e7d0f8de48ff88b8f982949 extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = array (
  'orchestrator_activeVar' => 'activeVar',
  'orchestrator_inactiveVar' => 'inactiveVar',
);
            $this->rewriteRules = array (
  '^(?|orchestrator/active/([^/]+))$' => 'index.php?orchestrator_activeVar=$matches[1]&orchestrator___routeType=variable',
  '^(?|orchestrator/inactive/([^/]+))$' => 'index.php?orchestrator_inactiveVar=$matches[1]&orchestrator___routeType=variable',
  '^(?|orchestrator/responder)$' => 'index.php?orchestrator___routeType=static',
  '^(?|orchestrator/hierarchical-responder)$' => 'index.php?orchestrator___routeType=static',
);

            $rewrite0 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|orchestrator/active/([^/]+))$', 'index.php?orchestrator_activeVar=$matches[1]&orchestrator___routeType=variable', array (
  'orchestrator_activeVar' => 'activeVar',
), static function () {}, NULL);
$rewrite1 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|orchestrator/inactive/([^/]+))$', 'index.php?orchestrator_inactiveVar=$matches[1]&orchestrator___routeType=variable', array (
  'orchestrator_inactiveVar' => 'inactiveVar',
), static function () {
            add_action('twr_test_data', function () {
                echo '<span class="twr-orchestrator-inactive"></span>';
            });
        }, '__return_false');
$rewrite2 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|orchestrator/responder)$', 'index.php?orchestrator___routeType=static', array (
), static function () {
            return new \ToyWpRouting\Responder\JsonResponder('hello from the orchestrator responder route');
        }, NULL);
$rewrite3 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|orchestrator/hierarchical-responder)$', 'index.php?orchestrator___routeType=static', array (
), static function () {
            $responder = new \ToyWpRouting\Responder\JsonResponder('hello from the orchestrator hierarchical responder route');

            // We return the headers partial - expectation is that orchestrator traverses back up to the JsonResponder.
            return $responder->getPartialSet()->get(\ToyWpRouting\Responder\Partial\HeadersPartial::class);
        }, NULL);
$this->rewritesByRegexAndMethod = array (
  '^(?|orchestrator/active/([^/]+))$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  '^(?|orchestrator/inactive/([^/]+))$' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
  ),
  '^(?|orchestrator/responder)$' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
  ),
  '^(?|orchestrator/hierarchical-responder)$' => 
  array (
    'GET' => $rewrite3,
    'HEAD' => $rewrite3,
  ),
);
        }
    }
}

return new CachedRewriteCollection3c95e90111a6fb2a7b1c9af68547483e1cb40c675e7d0f8de48ff88b8f982949();
