<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder;

interface HierarchicalResponderInterface extends ResponderInterface
{
    public function getParent(): ?ResponderInterface;
}
