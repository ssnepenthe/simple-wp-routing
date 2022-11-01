<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use ToyWpRouting\RewriteCollection;

class RewriteCollectionCompiler
{
    private const TEMPLATE = <<<'TPL'
    <?php

    declare(strict_types=1);

    return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
        return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
        {
            protected bool $locked = true;

            public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
            {
                parent::__construct(%s, $invocationStrategy);

                %s
            }
        };
    };

    TPL;

    private RewriteCollection $rewriteCollection;

    public function __construct(RewriteCollection $rewriteCollection)
    {
        $this->rewriteCollection = $rewriteCollection;
    }

    public function __toString(): string
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return sprintf(self::TEMPLATE, $this->prefix(), $this->rewrites());
    }

    private function prefix(): string
    {
        return var_export($this->rewriteCollection->getPrefix(), true);
    }

    private function rewrites(): string
    {
        return (string) (new RewriteListDefinitionsCompiler(
            iterator_to_array($this->rewriteCollection->getRewrites())
        ));
    }
}
