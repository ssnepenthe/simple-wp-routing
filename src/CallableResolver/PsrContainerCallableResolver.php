<?php

declare(strict_types=1);

namespace SimpleWpRouting\CallableResolver;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use SimpleWpRouting\Exception\BadCallableException;

final class PsrContainerCallableResolver implements CallableResolverInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param mixed $value
     *
     * @throws BadCallableException
     */
    public function resolve($value): callable
    {
        $toResolve = $value;

        if (is_string($toResolve) && false !== strpos($toResolve, '::')) {
            $toResolve = explode('::', $toResolve, 2);
        }

        if (is_callable($toResolve)) {
            // Return before resorting to reflection for common cases - closures, invokable objects, named functions.
            if (is_object($toResolve) || is_string($toResolve)) {
                return $toResolve;
            }

            // Necessary as long as we wish to continue supporting php 7.4.
            if (! $this->isStaticCallToNonStaticMethod($toResolve)) {
                return $toResolve;
            }
        }

        $resolved = null;

        if (is_string($toResolve) && $this->container->has($toResolve)) {
            $resolved = $this->container->get($toResolve);
        } elseif (is_array($toResolve) && is_string($id = ($toResolve[0] ?? null)) && $this->container->has($id)) {
            $resolved = [$this->container->get($id), $toResolve[1] ?? null];
        }

        if (! is_callable($resolved)) {
            throw new BadCallableException('Unable to resolve callable for value ' . var_export($value, true));
        }

        return $resolved;
    }

    private function isStaticCallToNonStaticMethod(callable $callable): bool
    {
        if (! is_array($callable)) {
            return false;
        }

        if (! is_string($callable[0])) {
            return false;
        }

        return ! (new ReflectionMethod($callable[0], $callable[1]))->isStatic();
    }
}
