<?php

declare(strict_types=1);

namespace SimpleWpRouting\Responder\Partial;

interface RegistersConflictsInterface
{
    public function registerConflicts(Conflicts $conflicts): void;
}
