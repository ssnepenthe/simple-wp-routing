<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Compiler\RewriteCollectionCompiler;
use ToyWpRouting\RewriteCollection;

class RewriteCollectionCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompile()
    {
        $rewrites = new RewriteCollection();

        $rewrites->post('^regex$', 'index.php?some=var', function () {
        });

        $this->assertMatchesSnapshot((new RewriteCollectionCompiler($rewrites))->compile());
    }
}
