<?php

declare(strict_types=1);

namespace ToyWpRouting;

use Closure;
use Opis\Closure\SerializableClosure;

// @todo Does not validate anything from cache.
class RewriteCollectionCache
{
    protected $dir;
    protected $file;

    public function __construct(string $dir, string $file = 'rewrite-cache.php')
    {
        $this->dir = $dir;
        $this->file = $file;
    }

    public function delete()
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
        $rewrites = static::staticInclude("{$this->dir}/{$this->file}");
        $rewriteCollection = new RewriteCollection();

        foreach ($rewrites as $rewriteArray) {
            if ($this->isSerializedClosure($rewriteArray['handler'])) {
                $rewriteArray['handler'] = unserialize($rewriteArray['handler'])->getClosure();
            }

            if ($this->isSerializedClosure($rewriteArray['isActiveCallback'])) {
                $rewriteArray['isActiveCallback'] = unserialize(
                    $rewriteArray['isActiveCallback']
                )->getClosure();
            }

            $rules = array_map(fn ($rule) => new OptimizedRewriteRule(
                $rule['hash'],
                $rule['prefixedQueryArray'],
                $rule['query'],
                $rule['queryArray'],
                $rule['regex']
            ), $rewriteArray['rules']);

            $rewriteCollection->add(
                new OptimizedRewrite(
                    $rewriteArray['methods'],
                    $rewriteArray['rewriteRules'],
                    $rules,
                    $rewriteArray['handler'],
                    $rewriteArray['prefixedToUnprefixedQueryVariablesMap'],
                    $rewriteArray['queryVariables'],
                    $rewriteArray['isActiveCallback']
                )
            );
        }

        // @todo lock?

        return $rewriteCollection;
    }

    public function put(RewriteCollection $rewriteCollection)
    {
        if (! file_exists($this->dir)) {
            mkdir($this->dir, 0700, true);
        }

        $this->delete();

        $rewrites = array_map(function (RewriteInterface $rewrite) {
            $handler = $rewrite->getHandler();

            if ($handler instanceof Closure) {
                $handler = serialize(new SerializableClosure($handler->bindTo(null, null)));
            }

            $isActiveCallback = $rewrite->getIsActiveCallback();

            if ($isActiveCallback instanceof Closure) {
                $isActiveCallback = serialize(
                    new SerializableClosure($isActiveCallback->bindTo(null, null))
                );
            }

            $prefixedToUnprefixedQVMap = $rewrite->getPrefixedToUnprefixedQueryVariablesMap();

            $rules = array_map(fn (RewriteRuleInterface $rule) => [
                'hash' => $rule->getHash(),
                'prefixedQueryArray' => $rule->getPrefixedQueryArray(),
                'query' => $rule->getQuery(),
                'queryArray' => $rule->getQueryArray(),
                'regex' => $rule->getRegex(),
            ], $rewrite->getRules());

            return [
                'methods' => $rewrite->getMethods(),
                'rewriteRules' => $rewrite->getRewriteRules(),
                'rules' => $rules,
                'handler' => $handler,
                'prefixedToUnprefixedQueryVariablesMap' => $prefixedToUnprefixedQVMap,
                'queryVariables' => $rewrite->getQueryVariables(),
                'isActiveCallback' => $isActiveCallback,
            ];
        }, iterator_to_array($rewriteCollection->getRewrites()));

        $eol = PHP_EOL;
        $exportedRewrites = var_export($rewrites, true);

        file_put_contents(
            "{$this->dir}/{$this->file}",
            "<?php{$eol}{$eol}return {$exportedRewrites};{$eol}"
        );
    }

    protected function isSerializedClosure($value)
    {
        return is_string($value)
            && 'C:32:"Opis\Closure\SerializableClosure"' === substr($value, 0, 39);
    }

    protected static function staticInclude(string $file)
    {
        return include $file;
    }
}
