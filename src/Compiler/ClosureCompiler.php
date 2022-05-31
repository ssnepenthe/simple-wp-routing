<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use Closure;
use Opis\Closure\ReflectionClosure;
use RuntimeException;

class ClosureCompiler
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __toString()
    {
        return $this->compile();
    }

    public function compile(): string
    {
        $ref = new ReflectionClosure($this->closure);

        if (! empty($ref->getUseVariables())) {
            throw new RuntimeException('@todo'); // Import with use keyword
        }

        if ($ref->isBindingRequired() || $ref->isScopeRequired()) {
            throw new RuntimeException('@todo'); // $this, self, static, parent
        }

        $code = trim($ref->getCode(), "\t\n\r\0\x0B;");

        return $ref->isStatic() ? $code : "static {$code}";
    }
}
