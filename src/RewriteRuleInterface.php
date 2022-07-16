<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface RewriteRuleInterface
{
    public function getHash(): string;

    public function getQuery(): string;

    /**
     * @return array<string, string>
     */
    public function getQueryVariables(): array;

    public function getRegex(): string;
}
