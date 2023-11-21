<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder;

interface HierarchicalResponderInterface extends ResponderInterface
{
    public function getParent(): ?ResponderInterface;
}
