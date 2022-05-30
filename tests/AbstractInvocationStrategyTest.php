<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\AbstractInvocationStrategy;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteInterface;
use ToyWpRouting\RewriteRule;

class AbstractInvocationStrategyTest extends TestCase
{
    public function testResolveCallable()
    {
        $strategy = $this->createInvocationStrategy();

        // Values are unmodified by default.
        $this->assertSame('originalvalue', $strategy->resolveCallableProxy('originalvalue'));

        $strategy->setCallableResolver(function ($potentialCallable) {
            if ('modifyme' === $potentialCallable) {
                return 'modifiedvalue';
            }

            return $potentialCallable;
        });

        $this->assertSame('originalvalue', $strategy->resolveCallableProxy('originalvalue'));
        $this->assertSame('modifiedvalue', $strategy->resolveCallableProxy('modifyme'));
    }

    public function testResolveRelevantQueryVariablesFromAdditionalContext()
    {
        $strategy = $this->createInvocationStrategy()
            ->withContext(['queryVars' => [
                'someqv' => 'someval',
                'unusedqv' => 'unusedval',
            ]]);

        $this->assertSame(
            ['someqv' => 'someval'],
            $strategy->relevantQueryVariablesProxy(
                new Rewrite(
                    [],
                    [new RewriteRule('^one$', 'index.php?someqv=someval')],
                    function () {
                    }
                )
            )
        );
    }

    public function testResolveRelevantQueryVariablesFromAdditionalContextWithNoQueryVarsSet()
    {
        $strategy = $this->createInvocationStrategy();

        $this->assertSame(
            [],
            $strategy->relevantQueryVariablesProxy(new Rewrite([], [], function () {
            }))
        );
    }

    public function testResolveRelevantQueryVariablesFromAdditionalContextWithPrefix()
    {
        $strategy = $this->createInvocationStrategy()
            ->withContext(['queryVars' => [
                'pfx_someqv' => 'someval',
                'pfx_unusedqv' => 'unusedval',
            ]]);

        $this->assertSame(
            ['someqv' => 'someval'],
            $strategy->relevantQueryVariablesProxy(
                new Rewrite(
                    [],
                    [new RewriteRule('^one$', 'index.php?someqv=someval', 'pfx_')],
                    function () {
                    }
                )
            )
        );
    }

    public function testWithAdditionalContext()
    {
        $strategy = $this->createInvocationStrategy();
        $withContext = $strategy->withAdditionalContext(['one' => 'two']);
        $additionalContext = $withContext->withAdditionalContext(['three' => 'four']);

        $this->assertInstanceOf(AbstractInvocationStrategy::class, $withContext);
        $this->assertInstanceOf(AbstractInvocationStrategy::class, $additionalContext);
        $this->assertSame([], $strategy->getContext());
        $this->assertSame(['one' => 'two'], $withContext->getContext());
        $this->assertSame(['one' => 'two', 'three' => 'four'], $additionalContext->getContext());
        $this->assertNotSame($strategy, $withContext);
        $this->assertNotSame($withContext, $additionalContext);
    }

    public function testWithContext()
    {
        $strategy = $this->createInvocationStrategy();
        $withContext = $strategy->withContext(['one' => 'two']);
        $additionalContext = $withContext->withContext(['three' => 'four']);

        $this->assertInstanceOf(AbstractInvocationStrategy::class, $withContext);
        $this->assertInstanceOf(AbstractInvocationStrategy::class, $additionalContext);
        $this->assertSame([], $strategy->getContext());
        $this->assertSame(['one' => 'two'], $withContext->getContext());
        $this->assertSame(['three' => 'four'], $additionalContext->getContext());
        $this->assertNotSame($strategy, $withContext);
        $this->assertNotSame($withContext, $additionalContext);
    }

    private function createInvocationStrategy()
    {
        return new class () extends AbstractInvocationStrategy {
            public function invokeHandler(RewriteInterface $rewrite)
            {
                //
            }

            public function invokeIsActiveCallback(RewriteInterface $rewrite)
            {
                //
            }

            public function resolveCallableProxy($callable)
            {
                return $this->resolveCallable($callable);
            }

            public function relevantQueryVariablesProxy(RewriteInterface $rewrite)
            {
                return $this->resolveRelevantQueryVariablesFromContext($rewrite);
            }
        };
    }
}
