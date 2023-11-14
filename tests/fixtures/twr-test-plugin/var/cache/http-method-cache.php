<?php

declare(strict_types=1);

return function (): \ToyWpRouting\RewriteCollection {
    return new class() extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct()
        {
            $this->queryVariables = array (
);
            $this->rewriteRules = array (
  '^(?|http-method/any)$' => 'index.php?httpmethod___routeType=static',
  '^(?|http-method/delete)$' => 'index.php?httpmethod___routeType=static',
  '^(?|http-method/get)$' => 'index.php?httpmethod___routeType=static',
  '^(?|http-method/options)$' => 'index.php?httpmethod___routeType=static',
  '^(?|http-method/patch)$' => 'index.php?httpmethod___routeType=static',
  '^(?|http-method/post)$' => 'index.php?httpmethod___routeType=static',
  '^(?|http-method/put)$' => 'index.php?httpmethod___routeType=static',
);

            $rewrite0 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
  2 => 'POST',
  3 => 'PUT',
  4 => 'PATCH',
  5 => 'DELETE',
  6 => 'OPTIONS',
), '^(?|http-method/any)$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite1 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'DELETE',
), '^(?|http-method/delete)$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite2 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^(?|http-method/get)$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite3 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'OPTIONS',
), '^(?|http-method/options)$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite4 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'PATCH',
), '^(?|http-method/patch)$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite5 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'POST',
), '^(?|http-method/post)$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$rewrite6 = new \ToyWpRouting\Dumper\OptimizedRewrite(array (
  0 => 'PUT',
), '^(?|http-method/put)$', 'index.php?httpmethod___routeType=static', array (
), static function () {}, NULL);
$this->rewritesByRegexAndMethod = array (
  '^(?|http-method/any)$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
    'POST' => $rewrite0,
    'PUT' => $rewrite0,
    'PATCH' => $rewrite0,
    'DELETE' => $rewrite0,
    'OPTIONS' => $rewrite0,
  ),
  '^(?|http-method/delete)$' => 
  array (
    'DELETE' => $rewrite1,
  ),
  '^(?|http-method/get)$' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
  ),
  '^(?|http-method/options)$' => 
  array (
    'OPTIONS' => $rewrite3,
  ),
  '^(?|http-method/patch)$' => 
  array (
    'PATCH' => $rewrite4,
  ),
  '^(?|http-method/post)$' => 
  array (
    'POST' => $rewrite5,
  ),
  '^(?|http-method/put)$' => 
  array (
    'PUT' => $rewrite6,
  ),
);
        }

        public function getRewrites(): array
        {
            throw new LogicException('Rewrites list not accessible on cache rewrite collection');
        }
    };
};
