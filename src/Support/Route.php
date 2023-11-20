<?php

declare(strict_types=1);

namespace SimpleWpRouting\Support;

final class Route
{
    /**
     * @var Rewrite[]
     */
    private array $rewrites;

    public function __construct(Rewrite ...$rewrites)
    {
        $this->rewrites = $rewrites;
    }

    /**
     * @return Rewrite[]
     */
    public function getRewrites(): array
    {
        return $this->rewrites;
    }

    /**
     * @param mixed $isActiveCallback
     */
    public function setIsActiveCallback($isActiveCallback): void
    {
        foreach ($this->rewrites as $rewrite) {
            $rewrite->setIsActiveCallback($isActiveCallback);
        }
    }
}
