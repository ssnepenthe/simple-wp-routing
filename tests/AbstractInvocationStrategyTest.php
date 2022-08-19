<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\AbstractInvocationStrategy;

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

    private function createInvocationStrategy()
    {
        return new class () extends AbstractInvocationStrategy {
            public function invoke($callable, array $context = [])
            {
                //
            }

            public function resolveCallableProxy($callable)
            {
                return $this->resolveCallable($callable);
            }
        };
    }
}
