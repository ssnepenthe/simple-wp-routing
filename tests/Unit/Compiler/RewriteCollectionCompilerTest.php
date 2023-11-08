<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Compiler\RewriteCollectionCompiler;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;

class RewriteCollectionCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompile()
    {
        $rewrites = new RewriteCollection();
        $rewrite = new Rewrite(['POST'], '^regex$', 'index.php?some=var', ['some' => 'some'], function () {
        });

        $rewrites->add($rewrite);

        $this->assertMatchesSnapshot((new RewriteCollectionCompiler($rewrites))->compile());
    }
}
