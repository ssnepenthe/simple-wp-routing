<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface RouteParserInterface
{
    /**
     * @return array{0: string, 1: array}
     */
    public function parse(string $route): array;
}
