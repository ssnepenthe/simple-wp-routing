<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;
use ToyWpRouting\Responder\HierarchicalResponderInterface;
use ToyWpRouting\Responder\ResponderInterface;
use Traversable;

final class PartialSet implements HierarchicalResponderInterface, IteratorAggregate
{
    /**
     * @var array<string, array{0: array, 1: array}>
     */
    private array $conflicts = [];
    /**
     * @var array<class-string<PartialInterface>, PartialInterface>
     */
    private array $partials = [];
    private ?ResponderInterface $responder;

    public function __construct(?ResponderInterface $responder = null)
    {
        $this->responder = $responder;
    }

    public function add(PartialInterface ...$partials): void
    {
        foreach ($partials as $partial) {
            $partial->setParent($this);

            if ($partial instanceof RegistersConflictsInterface) {
                $partial->registerConflicts($this);
            }

            $this->partials[get_class($partial)] = $partial;
        }
    }

    /**
     * @psalm-param array{0: class-string<PartialInterface>, 1: string} $one
     * @psalm-param array{0: class-string<PartialInterface>, 1: string} $two
     */
    public function addConflict(array $one, array $two): void
    {
        // @todo ???
        // $this->assertWellFormedConflict($one);
        // $this->assertWellFormedConflict($two);

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
    }

    public function assertNoConflicts(): void
    {
        foreach ($this->conflicts as [$one, $two]) {
            if (
                $this->has($one[0]) && call_user_func([$this->get($one[0]), $one[1]])
                && $this->has($two[0]) && call_user_func([$this->get($two[0]), $two[1]])
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
     * @template T of PartialInterface
     *
     * @psalm-param class-string<T> $class
     *
     * @psalm-return T
     */
    public function get(string $class): PartialInterface
    {
        /** @var array<class-string<T>, T> */
        $partials = $this->partials;

        if (array_key_exists($class, $partials)) {
            return $partials[$class];
        }

        throw new InvalidArgumentException("PartialSet does not contain partial \"{$class}\"");
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->partials);
    }

    public function getParent(): ?ResponderInterface
    {
        return $this->responder;
    }

    public function has(string $class): bool
    {
        return array_key_exists($class, $this->partials);
    }

    public function respond(): void
    {
        add_action('template_redirect', [$this, 'assertNoConflicts'], -99);

        foreach ($this->partials as $partial) {
            $partial->respond();
        }
    }
}
