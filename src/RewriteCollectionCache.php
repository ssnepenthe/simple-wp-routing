<?php

declare(strict_types=1);

namespace ToyWpRouting;

use Closure;
use ToyWpRouting\Compiler\RewriteCollectionCompiler;

class RewriteCollectionCache
{
    protected string $dir;

    protected string $file;

    public function __construct(string $dir, string $file = 'rewrite-cache.php') {
        $this->dir = $dir;
        $this->file = $file;
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

        return $factory();
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
}
