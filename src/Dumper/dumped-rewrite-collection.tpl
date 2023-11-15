<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('%s')) {
    class %s extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = %s;
            $this->rewriteRules = %s;

            %s
        }
    }
}

return new %s();
