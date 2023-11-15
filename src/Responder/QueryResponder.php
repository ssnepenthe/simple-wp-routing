<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder;

use SimpleWpRouting\Responder\Partial\WpPartial;

final class QueryResponder extends Responder
{
    /**
     * @param array<string, string> $queryVariables
     */
    public function __construct(array $queryVariables, bool $overwriteExisting = false)
    {
        $wp = $this->getPartialSet()->get(WpPartial::class);

        $wp->setQueryVariables($queryVariables);

        if ($overwriteExisting) {
            $wp->overwriteQueryVariables();
        }
    }
}
