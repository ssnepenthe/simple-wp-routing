<?php

declare(strict_types=1);

namespace ToyWpRouting;

// @todo toArray(), fromArray() methods?
interface RewriteRuleInterface
{
    public function getHash(): string;

    /**
     * @return array<string, string>
     */
    public function getPrefixedQueryArray(): array;

    public function getQuery(): string;

    /**
     * @return array<string, string>
     */
    public function getQueryArray(): array;

    public function getRegex(): string;
}
