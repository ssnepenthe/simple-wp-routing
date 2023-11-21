<?php

declare(strict_types=1);

namespace SimpleWpRouting\Parser;

interface RouteParserInterface
{
    /**
     * @return array<string, array<string, string>>
     */
    public function parse(string $route): array;
}
