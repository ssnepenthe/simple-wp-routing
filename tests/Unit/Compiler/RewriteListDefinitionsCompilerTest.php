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
        $one = new Rewrite(['GET', 'HEAD'], '^getregex$', 'index.php?pfx_var=get', ['pfx_var' => 'var'], function () {
        });
        $one->setIsActiveCallback(function () {
        });

        $two = new Rewrite(['POST'], '^postregex$', 'index.php?pfx_var=post', ['pfx_var' => 'var'], function () {
        });

        $this->assertMatchesSnapshot((new RewriteListDefinitionsCompiler([$one, $two]))->compile());
    }
}
