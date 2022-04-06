<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface RouteParserInterface
{
    public function parse(string $route): array;
}
