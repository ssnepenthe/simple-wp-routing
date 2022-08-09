<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

use ToyWpRouting\Responder\Concerns\ModifiesWpParameters;

class QueryResponder extends HookDrivenResponder
{
    use ModifiesWpParameters;

    public function __construct(array $queryVariables, bool $overwriteExisting = false)
    {
        $this->withPreLoopQueryVariables($queryVariables);

        if ($overwriteExisting) {
            $this->withExistingPreLoopQueryVariablesOverwritten();
        }
    }
}