<?php

declare(strict_types=1);

return function (): \ToyWpRouting\RewriteCollection {
    return new class() extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct()
        {
            parent::__construct();

            $this->queryVariables = array (
  'httpmethod___routeType' => '__routeType',
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

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
  2 => 'POST',
  3 => 'PUT',
  4 => 'PATCH',
  5 => 'DELETE',
  6 => 'OPTIONS',
), array (
  'httpmethod___routeType' => '__routeType',
), array (
  0 => 'httpmethod___routeType',
), static function () {}, NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'DELETE',
), array (
  'httpmethod___routeType' => '__routeType',
), array (
  0 => 'httpmethod___routeType',
), static function () {}, NULL);
$rewrite2 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'httpmethod___routeType' => '__routeType',
), array (
  0 => 'httpmethod___routeType',
), static function () {}, NULL);
$rewrite3 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'OPTIONS',
), array (
  'httpmethod___routeType' => '__routeType',
), array (
  0 => 'httpmethod___routeType',
), static function () {}, NULL);
$rewrite4 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'PATCH',
), array (
  'httpmethod___routeType' => '__routeType',
), array (
  0 => 'httpmethod___routeType',
), static function () {}, NULL);
$rewrite5 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'POST',
), array (
  'httpmethod___routeType' => '__routeType',
), array (
  0 => 'httpmethod___routeType',
), static function () {}, NULL);
$rewrite6 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'PUT',
), array (
  'httpmethod___routeType' => '__routeType',
), array (
  0 => 'httpmethod___routeType',
), static function () {}, NULL);
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
$this->rewrites->attach($rewrite2);
$this->rewrites->attach($rewrite3);
$this->rewrites->attach($rewrite4);
$this->rewrites->attach($rewrite5);
$this->rewrites->attach($rewrite6);
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
    };
};
