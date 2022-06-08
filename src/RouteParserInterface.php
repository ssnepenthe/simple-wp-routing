<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface RouteParserInterface
{
    /**
     * @return array<string, string>
     */
    public function parse(string $route): array;
}
