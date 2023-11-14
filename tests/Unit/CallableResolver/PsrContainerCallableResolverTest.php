<?php

namespace ToyWpRouting\Tests\Unit\CallableResolver;

use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ToyWpRouting\CallableResolver\PsrContainerCallableResolver;
use ToyWpRouting\Exception\BadCallableException;

class PsrContainerCallableResolverTest extends TestCase
{
    /** @dataProvider providesTestResolveAlreadyCallable */
    public function testResolveAlreadyCallable($callable, $expected)
    {
        $resolver = new PsrContainerCallableResolver(new Container());

        $this->assertSame($expected, $resolver->resolve($callable));
    }

    /** @dataProvider providesTestResolveStringFromContainer */
    public function testResolveStringFromContainer($value)
    {
        $container = new Container();
        $resolver = new PsrContainerCallableResolver($container);

        $this->assertSame($container->get($value), $resolver->resolve($value));
    }

    /** @dataProvider providesTestResolveArrayFromContainer */
    public function testResolveArrayFromContainer($value)
    {
        $container = new Container();
        $resolver = new PsrContainerCallableResolver($container);

        $this->assertSame([$container->get($value[0]), $value[1]], $resolver->resolve($value));
    }

    /** @dataProvider providesTestResolveThrowsForBadCallables */
    public function testResolveThrowsForBadCallables($value)
    {
        $this->expectException(BadCallableException::class);
        $this->expectExceptionMessage('Unable to resolve callable for value');

        $resolver = new PsrContainerCallableResolver(new Container());
        $resolver->resolve($value);
    }

    public static function providesTestResolveAlreadyCallable()
    {
        $namedFunc = __NAMESPACE__ . '\\psrTestFunc';
        yield [$namedFunc, $namedFunc];

        $closure = fn () => '';
        yield [$closure, $closure];

        $invokable = new PsrAvailableInContainer();
        yield [$invokable, $invokable];

        $staticString = PsrAvailableInContainer::class . '::staticTestMethod';
        $staticArray = [PsrAvailableInContainer::class, 'staticTestMethod'];
        yield [$staticString, $staticArray];
        yield [$staticArray, $staticArray];
    }

    public static function providesTestResolveStringFromContainer()
    {
        yield ['test.psrTestFunc'];
        yield ['test.closure'];
        yield ['test.PsrAvailableInContainer'];
    }

    public static function providesTestResolveArrayFromContainer()
    {
        yield [['test.PsrAvailableInContainer', '__invoke']];
        yield [['test.PsrAvailableInContainer', 'staticTestMethod']];
        yield [['test.PsrAvailableInContainer', 'nonStaticTestMethod']];
    }

    public static function providesTestResolveThrowsForBadCallables()
    {
        // String:
        // Not in container.
        yield ['test.PsrNotAvailableInContainer'];
        // Not callable
        yield ['test.notCallable'];

        // Array:
        // Index 0 must be string to be resolved from container.
        yield [[5, 'methodName']];
        // Index 0 is not able to be resolved from container and not a valid class name.
        yield [['test.PsrNotAvailableInContainer', 'nonStaticTestMethod']];
        // Index 0 is able to be resolved from container but resulting value is not callable.
        yield [['test.PsrAvailableInContainer', 'nonExistentMethod']];
        // Index 0 is able to be resolved from container but method uses key 'method' instead of index 1.
        yield [['test.PsrAvailableInContainer', 'method' => 'nonStaticTestMethod']];
        // Index 0 is not able to be resolved from container but is a valid class name.
        // This reads as a static call to non-static method which is considered callable on PHP <= 7.4.
        yield [[PsrNotAvailableInContainer::class, 'nonStaticTestMethod']];
    }
}

function psrTestFunc() {}

class PsrAvailableInContainer {
    public function __invoke() {}
    public static function staticTestMethod() {}
    public function nonStaticTestMethod() {}
}

class PsrNotAvailableInContainer {
    public function nonStaticTestMethod() {}
}

class Container implements ContainerInterface
{
    private array $entries = [];

    public function __construct()
    {
        $this->entries = [
            'test.psrTestFunc' => __NAMESPACE__ . '\\psrTestFunc',
            'test.closure' => fn () => '',

            'test.PsrAvailableInContainer' => new PsrAvailableInContainer(),

            'test.notCallable' => 12,
        ];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->entries);
    }

    public function get(string $id)
    {
        if (! $this->has($id)) {
            throw new LogicException('Ya dun goofed');
        }

        return $this->entries[$id];
    }
}
