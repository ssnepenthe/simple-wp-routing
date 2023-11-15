<?php

declare(strict_types=1);

namespace SimpleWpRouting\Parser;

interface RouteParserInterface
{
    /**
     * @return array{0: string, 1: array<string, string>}
     */
    public function parse(string $route): array;
}
