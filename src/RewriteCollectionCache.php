<?php

declare(strict_types=1);

namespace ToyWpRouting;

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
        $factory = static::staticInclude("{$this->dir}/{$this->file}");

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

        // @todo Should we really delete by default?
        $this->delete();

        $compiled = (string) (new RewriteCollectionCompiler($rewriteCollection));

        file_put_contents("{$this->dir}/{$this->file}", $compiled);
    }

    public function setInvocationStrategy(InvocationStrategyInterface $invocationStrategy): self
    {
        $this->invocationStrategy = $invocationStrategy;

        return $this;
    }

    /**
     * @return mixed
     */
    protected static function staticInclude(string $file)
    {
        return include $file;
    }
}
