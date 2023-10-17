<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Partial\WpPartial;

class QueryResponder extends ComposableResponder
{
    public function __construct(array $queryVariables, bool $overwriteExisting = false)
    {
        $this->wp()->setQueryVariables($queryVariables);

        if ($overwriteExisting) {
            $this->wp()->overwriteQueryVariables();
        }
    }

    public function wp(): WpPartial
    {
        return $this->getPartialSet()->get(WpPartial::class);
    }

    protected function createPartials(): array
    {
        return [new WpPartial()];
    }
}
