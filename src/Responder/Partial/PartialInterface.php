<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use ToyWpRouting\Responder\HierarchicalResponderInterface;

interface PartialInterface extends HierarchicalResponderInterface
{
    public function setParent(PartialSet $partialSet): void;
}
