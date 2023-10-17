<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial\WpPartial;

class QueryResponder extends Responder
{
    public function __construct(array $queryVariables, bool $overwriteExisting = false)
    {
        $wp = $this->getPartialSet()->get(WpPartial::class);

        $wp->setQueryVariables($queryVariables);

        if ($overwriteExisting) {
            $wp->overwriteQueryVariables();
        }
    }
}
