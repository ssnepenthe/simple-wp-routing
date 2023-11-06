<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Compiler\RewriteListDefinitionsCompiler;
use ToyWpRouting\Rewrite;

class RewriteListDefinitionsCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompile()
    {
        $one = new Rewrite(['GET', 'HEAD'], '^getregex$', 'index.php?var=get', function () {
        }, 'pfx_');
        $one->setIsActiveCallback(function () {
        });

        $two = new Rewrite(['POST'], '^postregex$', 'index.php?var=post', function () {
        }, 'pfx_');

        $this->assertMatchesSnapshot((new RewriteListDefinitionsCompiler([$one, $two]))->compile());
    }
}
