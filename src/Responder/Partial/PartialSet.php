<?php

declare(strict_types=1);

namespace ToyWpRouting\Responder\Partial;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use ToyWpRouting\Responder\HierarchicalResponderInterface;
use ToyWpRouting\Responder\ResponderInterface;
use Traversable;

final class PartialSet implements HierarchicalResponderInterface, IteratorAggregate
{
    private Conflicts $conflicts;
    /**
     * @var array<class-string<PartialInterface>, PartialInterface>
     */
    private array $partials = [];
    private ?ResponderInterface $responder;

    public function __construct(?ResponderInterface $responder = null)
    {
        $this->responder = $responder;
        $this->conflicts = new Conflicts();
    }

    public function add(PartialInterface ...$partials): void
    {
        foreach ($partials as $partial) {
            $partial->setParent($this);

            if ($partial instanceof RegistersConflictsInterface) {
                $partial->registerConflicts($this->conflicts);
            }

            $this->partials[get_class($partial)] = $partial;
        }
    }

    public function assertNoConflicts(): void
    {
        $this->conflicts->assertNoConflicts($this);
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
        add_action('template_redirect', function () {
            $this->conflicts->assertNoConflicts($this);
        }, -99);

        foreach ($this->partials as $partial) {
            $partial->respond();
        }
    }
}
