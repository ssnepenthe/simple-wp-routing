<?php

declare(strict_types=1);

namespace SimpleWpRouting\Support;

use SimpleWpRouting\Dumper\OptimizedRewriteCollection;
use SimpleWpRouting\Dumper\RewriteCollectionDumper;

final class RewriteCollectionCache
{
    private string $dir;

    private string $file;

    public function __construct(string $dir, string $file = 'rewrite-cache.php')
    {
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

    public function get(): OptimizedRewriteCollection
    {
        /**
         * @psalm-suppress UnresolvableInclude
         */
        $loader = static fn (string $dir, string $file): OptimizedRewriteCollection => include "{$dir}/{$file}";

        return $loader($this->dir, $this->file);
    }

    public function put(RewriteCollection $rewriteCollection): void
    {
        if (! file_exists($this->dir)) {
            mkdir($this->dir, 0700, true);
        }

        $this->delete();

        $dumped = (string) (new RewriteCollectionDumper($rewriteCollection));

        file_put_contents("{$this->dir}/{$this->file}", $dumped);
    }
}
