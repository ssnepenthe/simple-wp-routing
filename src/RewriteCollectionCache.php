<?php

declare(strict_types=1);

namespace ToyWpRouting;

use Closure;
use ToyWpRouting\Compiler\RewriteCollectionCompiler;

class RewriteCollectionCache
{
    protected string $dir;

    protected string $file;

    protected InvocationStrategyInterface $invocationStrategy;

    public function __construct(
        string $dir,
        string $file = 'rewrite-cache.php',
        ?InvocationStrategyInterface $invocationStrategy = null
    ) {
        $this->dir = $dir;
        $this->file = $file;
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();
    }

    public function delete(): void
    {
        $file = "{$this->dir}/{$this->file}";

        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function exists(): bool
    {
        return is_readable("{$this->dir}/{$this->file}");
    }

    public function get(): RewriteCollection
    {
        /**
         * @psalm-suppress UnresolvableInclude
         */
        $loader = static fn (string $dir, string $file): Closure => include "{$dir}/{$file}";
        $factory = $loader($this->dir, $this->file);

        return $factory($this->getInvocationStrategy());
    }

    public function getInvocationStrategy(): InvocationStrategyInterface
    {
        return $this->invocationStrategy;
    }

    public function put(RewriteCollection $rewriteCollection): void
    {
        if (! file_exists($this->dir)) {
            mkdir($this->dir, 0700, true);
        }

        $this->delete();

        $compiled = (string) (new RewriteCollectionCompiler($rewriteCollection));

        file_put_contents("{$this->dir}/{$this->file}", $compiled);
    }

    public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy): self
    {
        $this->invocationStrategy = $invocationStrategy;

        return $this;
    }
}
