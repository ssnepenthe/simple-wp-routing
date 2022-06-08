<?php

declare(strict_types=1);

namespace ToyWpRouting;

interface RewriteInterface
{
    /**
     * @return mixed
     */
    public function getHandler();

    /**
     * @return mixed
     */
    public function getIsActiveCallback();

    /**
     * @return array<int, "GET"|"HEAD"|"POST"|"PUT"|"PATCH"|"DELETE"|"OPTIONS">
     */
    public function getMethods(): array;

    /**
     * @return array<string, string>
     */
    public function getPrefixedToUnprefixedQueryVariablesMap(): array;

    /**
     * @return string[]
     */
    public function getQueryVariables(): array;

    /**
     * @return array<string, string>
     */
    public function getRewriteRules(): array;

    /**
     * @return RewriteRuleInterface[]
     */
    public function getRules(): array;
}
