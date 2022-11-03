<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct('httpmethod_', $invocationStrategy);

            $this->queryVariables = array (
  'httpmethod_matchedRule' => 'matchedRule',
);
            $this->rewriteRules = array (
  '^http-method/any$' => 'index.php?httpmethod_matchedRule=3cbc29585c68496bd468da8d63561c39',
  '^http-method/delete$' => 'index.php?httpmethod_matchedRule=37d6cdf198a707bba145b00613058ef8',
  '^http-method/get$' => 'index.php?httpmethod_matchedRule=3d22b4271ebf8126f05ed77ad3479b4a',
  '^http-method/options$' => 'index.php?httpmethod_matchedRule=5d3e9032053234bfe214358c1290d72b',
  '^http-method/patch$' => 'index.php?httpmethod_matchedRule=9a9447688dc3400decb5201e18ba832a',
  '^http-method/post$' => 'index.php?httpmethod_matchedRule=0dee99f4ec0583503eb54f1c0a1f4af0',
  '^http-method/put$' => 'index.php?httpmethod_matchedRule=4121c49c7eeb10e3449db33ed47d011e',
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
  'httpmethod_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'DELETE',
), array (
  'httpmethod_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$rewrite2 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'httpmethod_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$rewrite3 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'OPTIONS',
), array (
  'httpmethod_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$rewrite4 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'PATCH',
), array (
  'httpmethod_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$rewrite5 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'POST',
), array (
  'httpmethod_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$rewrite6 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'PUT',
), array (
  'httpmethod_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
$this->rewrites->attach($rewrite2);
$this->rewrites->attach($rewrite3);
$this->rewrites->attach($rewrite4);
$this->rewrites->attach($rewrite5);
$this->rewrites->attach($rewrite6);
$this->rewritesByHashAndMethod = array (
  '3cbc29585c68496bd468da8d63561c39' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
    'POST' => $rewrite0,
    'PUT' => $rewrite0,
    'PATCH' => $rewrite0,
    'DELETE' => $rewrite0,
    'OPTIONS' => $rewrite0,
  ),
  '37d6cdf198a707bba145b00613058ef8' => 
  array (
    'DELETE' => $rewrite1,
  ),
  '3d22b4271ebf8126f05ed77ad3479b4a' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
  ),
  '5d3e9032053234bfe214358c1290d72b' => 
  array (
    'OPTIONS' => $rewrite3,
  ),
  '9a9447688dc3400decb5201e18ba832a' => 
  array (
    'PATCH' => $rewrite4,
  ),
  '0dee99f4ec0583503eb54f1c0a1f4af0' => 
  array (
    'POST' => $rewrite5,
  ),
  '4121c49c7eeb10e3449db33ed47d011e' => 
  array (
    'PUT' => $rewrite6,
  ),
);
        }
    };
};
