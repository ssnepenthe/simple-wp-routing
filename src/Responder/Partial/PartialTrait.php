<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use ToyWpRouting\Responder\ResponderInterface;

trait PartialTrait
{
    protected ?PartialSet $partialSet = null;

    public function getParent(): ?ResponderInterface
    {
        return $this->partialSet;
    }

    public function getPartialSet(): ?PartialSet
    {
        return $this->partialSet;
    }

    public function setPartialSet(PartialSet $partialSet): void
    {
        $this->partialSet = $partialSet;
    }
}
