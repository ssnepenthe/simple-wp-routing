<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct('orchestrator_', $invocationStrategy);

            $this->queryVariables = array (
  'orchestrator_activeVar' => 'activeVar',
  'orchestrator_matchedRule' => 'matchedRule',
  'orchestrator_inactiveVar' => 'inactiveVar',
);
            $this->rewriteRules = array (
  '^orchestrator/active/([^/]+)$' => 'index.php?orchestrator_activeVar=$matches[1]&orchestrator_matchedRule=5cc12d9280457964a1502740d21f1321',
  '^orchestrator/inactive/([^/]+)$' => 'index.php?orchestrator_inactiveVar=$matches[1]&orchestrator_matchedRule=ebfd581dad33307b76e52c6c6b23eb54',
  '^orchestrator/responder$' => 'index.php?orchestrator_matchedRule=29647899a9f4d0d2f996a1a22788cdbc',
  '^orchestrator/hierarchical-responder$' => 'index.php?orchestrator_matchedRule=fbf5e1e28ad12d9417cf86d7c4ec2bff',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'orchestrator_activeVar' => 'activeVar',
  'orchestrator_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {}, NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'orchestrator_inactiveVar' => 'inactiveVar',
  'orchestrator_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {
            add_action('twr_test_data', function () {
                echo '<span class="twr-orchestrator-inactive"></span>';
            });
        }, '__return_false');
$rewrite2 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'orchestrator_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {
            return new \ToyWpRouting\Responder\JsonResponder('hello from the orchestrator responder route');
        }, NULL);
$rewrite3 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'orchestrator_matchedRule' => 'matchedRule',
), $this->invocationStrategy, static function () {
            $responder = new \ToyWpRouting\Responder\JsonResponder('hello from the orchestrator hierarchical responder route');

            // We return the headers partial - expectation is that orchestrator traverses back up to the JsonResponder.
            return $responder->headers();
        }, NULL);
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
$this->rewrites->attach($rewrite2);
$this->rewrites->attach($rewrite3);
$this->rewritesByHashAndMethod = array (
  '5cc12d9280457964a1502740d21f1321' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  'ebfd581dad33307b76e52c6c6b23eb54' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
  ),
  '29647899a9f4d0d2f996a1a22788cdbc' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
  ),
  'fbf5e1e28ad12d9417cf86d7c4ec2bff' => 
  array (
    'GET' => $rewrite3,
    'HEAD' => $rewrite3,
  ),
);
        }
    };
};
