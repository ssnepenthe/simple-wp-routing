<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder\Partial;

use SimpleWpRouting\Responder\ResponderInterface;

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
