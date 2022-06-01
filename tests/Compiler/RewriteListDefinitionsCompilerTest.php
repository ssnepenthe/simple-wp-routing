<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Compiler\RewriteListDefinitionsCompiler;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteRule;

class RewriteListDefinitionsCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompile()
    {
        $one = new Rewrite(
            ['GET', 'HEAD'],
            [new RewriteRule('^getregex$', 'index.php?var=get', 'pfx_')],
            function () {
            }
        );
        $one->setIsActiveCallback(function () {
        });

        $two = new Rewrite(
            ['POST'],
            [new RewriteRule('^postregex$', 'index.php?var=post', 'pfx_')],
            function () {
            }
        );

        $this->assertMatchesSnapshot((new RewriteListDefinitionsCompiler([$one, $two]))->compile());
    }
}
