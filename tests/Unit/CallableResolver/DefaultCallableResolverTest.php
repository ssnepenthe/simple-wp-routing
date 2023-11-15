<?php

namespace SimpleWpRouting\Tests\Unit\CallableResolver;

use PHPUnit\Framework\TestCase;
use SimpleWpRouting\CallableResolver\DefaultCallableResolver;
use SimpleWpRouting\Exception\BadCallableException;

class DefaultCallableResolverTest extends TestCase
{
    /** @dataProvider providesTestResolveCallable */
    public function testResolveCallable($callable)
    {
        $resolver = new DefaultCallableResolver();

        $this->assertSame($callable, $resolver->resolve($callable));
    }

    public function testResolveThrowsForNonCallables()
    {
        $this->expectException(BadCallableException::class);
        $this->expectExceptionMessage('is not callable');

        $resolver = new DefaultCallableResolver();
        $resolver->resolve('notCallableString');
    }

    public static function providesTestResolveCallable()
    {
        yield [__NAMESPACE__ . '\\defaultTestFunc'];
        yield [fn () => ''];
        yield [new DefaultInvokable()];
        yield [DefaultInvokable::class . '::staticTestMethod'];
        yield [[DefaultInvokable::class, 'staticTestMethod']];
        yield [[new DefaultInvokable(), 'nonStaticTestMethod']];
    }
}

function defaultTestFunc() {}

class DefaultInvokable {
    public function __invoke() {}
    public static function staticTestMethod() {}
    public function nonStaticTestMethod() {}
}
