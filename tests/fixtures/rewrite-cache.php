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
  'var' => 'var',
);
            $this->rewriteRules = array (
  '^first$' => 'index.php?var=first',
  '^second$' => 'index.php?var=second',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'var' => 'var',
), array (
  0 => 'var',
), $invocationStrategy, 'firsthandler', NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'POST',
), array (
  'var' => 'var',
), array (
  0 => 'var',
), $invocationStrategy, 'secondhandler', 'secondisactivecallback');
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
$this->rewritesByRegexAndMethod = array (
  '^first$' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  '^second$' => 
  array (
    'POST' => $rewrite1,
  ),
);
        }
    };
};
