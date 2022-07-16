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
     * @return RewriteRuleInterface[]
     */
    public function getRules(): array;

    public function mapQueryVariable(string $queryVariable): ?string;
}
