<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct('', $invocationStrategy);

            $this->queryVariables = array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
);
            $this->rewriteRules = array (
  '^first$' => 'index.php?var=first&matchedRule=9f79cebcf1735d5eaefeee8dbc7316dd',
  '^second$' => 'index.php?var=second&matchedRule=3cf5d427e03a68a3881d2d68a86b64f1',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), $this->invocationStrategy, 'firsthandler', NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'POST',
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), $this->invocationStrategy, 'secondhandler', 'secondisactivecallback');
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
$this->rewritesByHashAndMethod = array (
  '9f79cebcf1735d5eaefeee8dbc7316dd' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  '3cf5d427e03a68a3881d2d68a86b64f1' => 
  array (
    'POST' => $rewrite1,
  ),
);
        }
    };
};
