<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder;

use SimpleWpRouting\Responder\Partial\PartialInterface;
use SimpleWpRouting\Responder\Partial\PartialSet;

abstract class ComposableResponder implements ResponderInterface
{
    protected ?PartialSet $partialSet = null;

    public function getPartialSet(): PartialSet
    {
        if (! $this->partialSet instanceof PartialSet) {
            $this->partialSet = new PartialSet($this);

            $this->partialSet->add(...$this->createPartials());
        }

        return $this->partialSet;
    }

    public function respond(): void
    {
        $this->getPartialSet()->respond();
    }

    /**
     * @return PartialInterface[]
     */
    abstract protected function createPartials(): array;
}
