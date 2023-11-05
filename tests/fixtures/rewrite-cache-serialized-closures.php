<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct();

            $invocationStrategy = $invocationStrategy ?: new \ToyWpRouting\DefaultInvocationStrategy();

            $this->queryVariables = array (
  'pfx_var' => 'var',
);
            $this->rewriteRules = array (
  '^regex$' => 'index.php?pfx_var=val',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'pfx_var' => 'var',
), array (
  0 => 'pfx_var',
), $invocationStrategy, static function () {}, static function () {});
$this->rewrites->attach($rewrite0);
$this->rewritesByRegexAndMethod = array (
  '^regex$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
);
        }
    };
};
