<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use stdClass;
use ToyWpRouting\Compiler\RewriteCompiler;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteRule;

class RewriteCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompileWithArrayCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?var=value', ['handlerclass', 'handlermethod'], 'pfx_');
        $rewrite->setIsActiveCallback(['isactiveclass', 'isactivemethod']);

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithClosureCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?var=value', function () {
        }, 'pfx_');
        $rewrite->setIsActiveCallback(function () {
        });

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithInvalidHandler()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(['GET'], '^regex$', 'index.php?var=val', [new stdClass(), 'methodname'], 'pfx_');

        (new RewriteCompiler($rewrite))->compile();
    }

    public function testCompileWithInvalidIsActiveCallback()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(['GET'], '^regex$', 'index.php?var=val', 'handler', 'pfx_');
        $rewrite->setIsActiveCallback([new stdClass(), 'methodname']);

        (new RewriteCompiler($rewrite))->compile();
    }

    public function testCompileWithNoIsActiveCallback()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?var=value', function () {
        }, 'pfx_');

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithStringCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?var=value', 'handler', 'pfx_');
        $rewrite->setIsActiveCallback('isactivecallback');

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }
}
