<?php

declare(strict_types=1);

namespace ToyWpRouting\Dumper;

use Closure;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use RuntimeException;

final class ClosureDumper
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __toString(): string
    {
        return $this->dump();
    }

    public function dump(): string
    {
        $ref = new ReflectionClosure($this->closure);

        if (! empty($ref->getUseVariables())) {
            throw new RuntimeException('Closures with "use" imports not supported');
        }

        if ($ref->isBindingRequired() || $ref->isScopeRequired()) {
            throw new RuntimeException(
                'Closures referencing "$this", "self", "static", or "parent" not supported'
            );
        }

        // Trim is probably not necessary...
        $code = trim($ref->getCode(), "\t\n\r\0\x0B;");

        return $ref->isStatic() ? $code : "static {$code}";
    }
}
