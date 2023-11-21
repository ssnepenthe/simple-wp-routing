<?php

declare(strict_types=1);

namespace SimpleWpRouting\Dumper;

use LogicException;
use SimpleWpRouting\Support\RewriteCollection;

class OptimizedRewriteCollection extends RewriteCollection
{
    protected bool $locked = true;

    public function getRewrites(): array
    {
        throw new LogicException('Rewrites list not accessible on cached rewrite collection');
    }
};
