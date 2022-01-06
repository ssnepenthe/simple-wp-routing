<?php

namespace ToyWpRouting;

interface RouteParserInterface
{
    public function parse(string $route): array;
}
