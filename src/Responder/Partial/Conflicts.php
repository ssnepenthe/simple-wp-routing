<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use InvalidArgumentException;
use RuntimeException;

final class Conflicts
{
    private array $conflicts = [];

    public function assertNoConflicts(PartialSet $partialSet): void
    {
        foreach ($this->conflicts as [$one, $two]) {
            if (
                $partialSet->has($one[0]) && call_user_func([$partialSet->get($one[0]), $one[1]])
                && $partialSet->has($two[0]) && call_user_func([$partialSet->get($two[0]), $two[1]])
            ) {
                throw new RuntimeException(sprintf(
                    'Conflicting responder state - %s::%s() and %s::%s() cannot both be true',
                    $one[0],
                    $one[1],
                    $two[0],
                    $two[1]
                ));
            }
        }
    }

    /**
     * @psalm-param array{0: class-string<PartialInterface>, 1: string} $one
     * @psalm-param array{0: class-string<PartialInterface>, 1: string} $two
     */
    public function register(array $one, array $two): self
    {
        $oneKey = "{$one[0]}::{$one[1]}";
        $twoKey = "{$two[0]}::{$two[1]}";

        $a = strcmp($oneKey, $twoKey);

        if ($a < 0) {
            $key = $oneKey . $twoKey;
        } elseif ($a > 0) {
            $key = $twoKey . $oneKey;
        } else {
            throw new InvalidArgumentException('Cannot add self referencing conflict');
        }

        if (! array_key_exists($key, $this->conflicts)) {
            $this->conflicts[$key] = [$one, $two];
        }

        return $this;
    }
}
