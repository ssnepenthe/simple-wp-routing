<?php

declare(strict_types=1);

namespace ToyWpRouting\Dumper;

use Closure;
use RuntimeException;
use ToyWpRouting\Rewrite;

class RewriteDumper
{
    private const TEMPLATE = 'new \\ToyWpRouting\\Dumper\\OptimizedRewrite(%s, %s, %s, %s, %s, %s)';

    private Rewrite $rewrite;

    public function __construct(Rewrite $rewrite)
    {
        $this->rewrite = $rewrite;
    }

    public function __toString(): string
    {
        return $this->dump();
    }

    public function dump(): string
    {
        return sprintf(
            self::TEMPLATE,
            $this->methods(),
            $this->regex(),
            $this->query(),
            $this->queryVariables(),
            $this->handler(),
            $this->isActiveCallback()
        );
    }

    /**
     * @param mixed $value
     */
    private function dumpCallbackIfSupported($value): string
    {
        if ($value instanceof Closure) {
            return (string) (new ClosureDumper($value));
        }

        if (is_string($value) || (
            is_array($value)
            && isset($value[0])
            && is_string($value[0])
            && isset($value[1])
            && is_string($value[1])
        )) {
            return var_export($value, true);
        }

        throw new RuntimeException(
            'Unsupported callback type - must be closure, string, or array{0: string, 1: string}'
        );
    }

    private function handler(): string
    {
        return $this->dumpCallbackIfSupported($this->rewrite->getHandler());
    }

    private function isActiveCallback(): string
    {
        if (! $this->rewrite->hasIsActiveCallback()) {
            return var_export(null, true);
        }

        return $this->dumpCallbackIfSupported($this->rewrite->getIsActiveCallback());
    }

    private function methods(): string
    {
        return var_export($this->rewrite->getMethods(), true);
    }

    private function query(): string
    {
        return var_export($this->rewrite->getQuery(), true);
    }

    private function queryVariables(): string
    {
        return var_export($this->rewrite->getQueryVariables(), true);
    }

    private function regex(): string
    {
        return var_export($this->rewrite->getRegex(), true);
    }
}
