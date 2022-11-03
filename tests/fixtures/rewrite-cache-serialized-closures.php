<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct('pfx_', $invocationStrategy);

            $this->queryVariables = array (
  'pfx_var' => 'var',
  'pfx_matchedRule' => 'matchedRule',
);
            $this->rewriteRules = array (
  '^regex$' => 'index.php?pfx_var=val&pfx_matchedRule=e8362b7488c4e1a7eee5ff88b032f6eb',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  0 => new \ToyWpRouting\Compiler\OptimizedRewriteRule('e8362b7488c4e1a7eee5ff88b032f6eb', 'index.php?pfx_var=val&pfx_matchedRule=e8362b7488c4e1a7eee5ff88b032f6eb', array (
  'pfx_var' => 'var',
  'pfx_matchedRule' => 'matchedRule',
), '^regex$'),
), array (
  'pfx_var' => 'var',
  'pfx_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, static function () {});
$this->rewrites->attach($rewrite0);
$this->rewritesByHashAndMethod = array (
  'e8362b7488c4e1a7eee5ff88b032f6eb' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
);
        }
    };
};
