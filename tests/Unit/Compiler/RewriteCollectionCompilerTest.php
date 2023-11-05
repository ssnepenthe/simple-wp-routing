<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Compiler\RewriteCollectionCompiler;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteRule;

class RewriteCollectionCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompile()
    {
        $rewrites = new RewriteCollection();
        $rule = new RewriteRule('^regex$', 'index.php?some=var');
        $rule->setRequiredQueryVariables(['some']);
        $rewrite = new Rewrite(['POST'], [$rule], function () {
        });

        $rewrites->add($rewrite);

        $this->assertMatchesSnapshot((new RewriteCollectionCompiler($rewrites))->compile());
    }
}
