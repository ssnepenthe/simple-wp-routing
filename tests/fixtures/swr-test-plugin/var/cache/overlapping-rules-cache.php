<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('CachedRewriteCollection5895ebc53a7efcf19032a0aeb9ca717b9ef1ed3f819c4544b663e1a67225a489')) {
    class CachedRewriteCollection5895ebc53a7efcf19032a0aeb9ca717b9ef1ed3f819c4544b663e1a67225a489 extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = array (
);
            $this->rewriteRules = array (
  '^overlap/one$' => 'index.php?overlap___routeType=static',
  '^overlap/one/two$' => 'index.php?overlap___routeType=static',
  '^overlap/one/three$' => 'index.php?overlap___routeType=static',
);

            $rewrite0 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^overlap/one$', 'index.php?overlap___routeType=static', array (
), static function () {
            return new \SimpleWpRouting\Responder\JsonResponder('GET overlap/one');
        }, NULL);
$rewrite1 = new OptimizedRewrite(array (
  0 => 'POST',
), '^overlap/one$', 'index.php?overlap___routeType=static', array (
), static function () {
            return new \SimpleWpRouting\Responder\JsonResponder('POST overlap/one');
        }, NULL);
$rewrite2 = new OptimizedRewrite(array (
  0 => 'POST',
), '^overlap/one/two$', 'index.php?overlap___routeType=static', array (
), static function () {
            return new \SimpleWpRouting\Responder\JsonResponder('POST overlap/one');
        }, NULL);
$rewrite3 = new OptimizedRewrite(array (
  0 => 'PUT',
), '^overlap/one$', 'index.php?overlap___routeType=static', array (
), static function () {
            return new \SimpleWpRouting\Responder\JsonResponder('PUT overlap/one');
        }, NULL);
$rewrite4 = new OptimizedRewrite(array (
  0 => 'PUT',
), '^overlap/one/three$', 'index.php?overlap___routeType=static', array (
), static function () {
            return new \SimpleWpRouting\Responder\JsonResponder('PUT overlap/one');
        }, NULL);
$this->rewritesByRegexAndMethod = array (
  '^overlap/one$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
    'POST' => $rewrite1,
    'PUT' => $rewrite3,
  ),
  '^overlap/one/two$' => 
  array (
    'POST' => $rewrite2,
  ),
  '^overlap/one/three$' => 
  array (
    'PUT' => $rewrite4,
  ),
);
        }
    }
}

return new CachedRewriteCollection5895ebc53a7efcf19032a0aeb9ca717b9ef1ed3f819c4544b663e1a67225a489();
