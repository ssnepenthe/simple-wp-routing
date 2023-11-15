<?php

declare(strict_types=1);

namespace ToyWpRouting\Dumper;

use LogicException;
use ToyWpRouting\Support\RewriteCollection;

class OptimizedRewriteCollection extends RewriteCollection
{
    protected bool $locked = true;

    public function getRewrites(): array
    {
        throw new LogicException('Rewrites list not accessible on cached rewrite collection');
    }
};
