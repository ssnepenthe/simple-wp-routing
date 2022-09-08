<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use ToyWpRouting\Responder\ResponderInterface;

trait PartialTrait
{
    private ?PartialSet $partialSet = null;

    public function getParent(): ?ResponderInterface
    {
        return $this->partialSet;
    }

    public function setParent(PartialSet $partialSet): void
    {
        $this->partialSet = $partialSet;
    }
}
