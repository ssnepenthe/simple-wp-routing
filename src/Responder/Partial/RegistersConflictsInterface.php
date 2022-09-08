<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

interface RegistersConflictsInterface
{
    public function registerConflicts(PartialSet $partialSet): void;
}
