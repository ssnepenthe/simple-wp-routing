<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null) {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct('', $invocationStrategy);

            $this->rewrites->attach(new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  0 => new \ToyWpRouting\Compiler\OptimizedRewriteRule('9f79cebcf1735d5eaefeee8dbc7316dd', 'index.php?var=first&matchedRule=9f79cebcf1735d5eaefeee8dbc7316dd', array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), '^first$'),
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), $this->invocationStrategy, 'firsthandler', NULL));
$this->rewrites->attach(new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'POST',
), array (
  0 => new \ToyWpRouting\Compiler\OptimizedRewriteRule('3cf5d427e03a68a3881d2d68a86b64f1', 'index.php?var=second&matchedRule=3cf5d427e03a68a3881d2d68a86b64f1', array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), '^second$'),
), array (
  'var' => 'var',
  'matchedRule' => 'matchedRule',
), $this->invocationStrategy, 'secondhandler', 'secondisactivecallback'));
        }
    };
};
