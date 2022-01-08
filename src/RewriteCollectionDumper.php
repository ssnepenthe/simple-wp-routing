<?php

namespace ToyWpRouting;

use Closure;
use Opis\Closure\SerializableClosure;

class RewriteCollectionDumper
{
    protected $rewriteCollection;

    public function __construct(RewriteCollection $rewriteCollection)
    {
        $this->rewriteCollection = $rewriteCollection;
    }

    public function toFile(string $dir, string $file = 'rewrite-cache.php')
    {
        if (! file_exists($dir)) {
            mkdir($dir, 0700, true);
        }

        $rewrites = array_map(function (array $rewrite) {
            if ($rewrite['handler'] instanceof Closure) {
                $rewrite['handler'] = serialize(
                    new SerializableClosure($rewrite['handler']->bindTo(null, null))
                );
            }

            if ($rewrite['isActiveCallback'] instanceof Closure) {
                $rewrite['isActiveCallback'] = serialize(
                    new SerializableClosure($rewrite['isActiveCallback']->bindTo(null, null))
                );
            }

            return $rewrite;
        }, $this->toArray());

        $eol = PHP_EOL;
        $exportedRewrites = var_export($rewrites, true);

        file_put_contents(
            "{$dir}/{$file}",
            "<?php{$eol}{$eol}return {$exportedRewrites};{$eol}"
        );
    }

    public function toArray()
    {
        return array_map(function (RewriteInterface $rewrite) {
            return [
                'methods' => $rewrite->getMethods(),
                'rules' => $rewrite->getRules(),
                'handler' => $rewrite->getHandler(),
                'prefixedToUnprefixedQueryVariablesMap' => $rewrite->getPrefixedToUnprefixedQueryVariablesMap(),
                'queryVariables' => $rewrite->getQueryVariables(),
                'isActiveCallback' => $rewrite->getIsActiveCallback(),
            ];
        }, $this->rewriteCollection->getRewrites());
    }
}
