<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('CachedRewriteCollection5060946aa6ba3a9bcfc0bf07bb830c0ec0c9bf0226c73542cbcd741812bbd633')) {
    class CachedRewriteCollection5060946aa6ba3a9bcfc0bf07bb830c0ec0c9bf0226c73542cbcd741812bbd633 extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = array (
);
            $this->rewriteRules = array (
  '^http-method/any$' => 'index.php?httpmethod___routeType=static',
  '^http-method/delete$' => 'index.php?httpmethod___routeType=static',
  '^http-method/get$' => 'index.php?httpmethod___routeType=static',
  '^http-method/options$' => 'index.php?httpmethod___routeType=static',
  '^http-method/patch$' => 'index.php?httpmethod___routeType=static',
  '^http-method/post$' => 'index.php?httpmethod___routeType=static',
  '^http-method/put$' => 'index.php?httpmethod___routeType=static',
);

            $rewrite0 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
  2 => 'POST',
  3 => 'PUT',
  4 => 'PATCH',
  5 => 'DELETE',
  6 => 'OPTIONS',
), '^http-method/any$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite1 = new OptimizedRewrite(array (
  0 => 'DELETE',
), '^http-method/delete$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite2 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^http-method/get$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite3 = new OptimizedRewrite(array (
  0 => 'OPTIONS',
), '^http-method/options$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite4 = new OptimizedRewrite(array (
  0 => 'PATCH',
), '^http-method/patch$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite5 = new OptimizedRewrite(array (
  0 => 'POST',
), '^http-method/post$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite6 = new OptimizedRewrite(array (
  0 => 'PUT',
), '^http-method/put$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$this->rewritesByRegexAndMethod = array (
  '^http-method/any$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
    'POST' => $rewrite0,
    'PUT' => $rewrite0,
    'PATCH' => $rewrite0,
    'DELETE' => $rewrite0,
    'OPTIONS' => $rewrite0,
  ),
  '^http-method/delete$' => 
  array (
    'DELETE' => $rewrite1,
  ),
  '^http-method/get$' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
  ),
  '^http-method/options$' => 
  array (
    'OPTIONS' => $rewrite3,
  ),
  '^http-method/patch$' => 
  array (
    'PATCH' => $rewrite4,
  ),
  '^http-method/post$' => 
  array (
    'POST' => $rewrite5,
  ),
  '^http-method/put$' => 
  array (
    'PUT' => $rewrite6,
  ),
);
        }
    }
}

return new CachedRewriteCollection5060946aa6ba3a9bcfc0bf07bb830c0ec0c9bf0226c73542cbcd741812bbd633();
