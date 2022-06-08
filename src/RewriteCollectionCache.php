<?php

declare(strict_types=1);

namespace ToyWpRouting;

use ToyWpRouting\Compiler\RewriteCollectionCompiler;

class RewriteCollectionCache
{
    protected string $dir;

    protected string $file;

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

    public function get(): RewriteCollection
    {
        return static::staticInclude("{$this->dir}/{$this->file}");
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

    /**
     * @return mixed
     */
    protected static function staticInclude(string $file)
    {
        return include $file;
    }
}
