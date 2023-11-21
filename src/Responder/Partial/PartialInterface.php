<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder\Partial;

use SimpleWpRouting\Responder\HierarchicalResponderInterface;

interface PartialInterface extends HierarchicalResponderInterface
{
    public function setParent(PartialSet $partialSet): void;
}
