<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use stdClass;
use ToyWpRouting\Compiler\RewriteCompiler;
use ToyWpRouting\Rewrite;

class RewriteCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompileWithArrayCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], ['handlerclass', 'handlermethod']);
        $rewrite->setIsActiveCallback(['isactiveclass', 'isactivemethod']);

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithClosureCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], function () {
        });
        $rewrite->setIsActiveCallback(function () {
        });

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithInvalidHandler()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(['GET'], '^regex$', 'index.php?pfx_var=val', ['pfx_var' => 'var'], [new stdClass(), 'methodname']);

        (new RewriteCompiler($rewrite))->compile();
    }

    public function testCompileWithInvalidIsActiveCallback()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(['GET'], '^regex$', 'index.php?var=val', ['var' => 'var'], 'handler', 'pfx_');
        $rewrite->setIsActiveCallback([new stdClass(), 'methodname']);

        (new RewriteCompiler($rewrite))->compile();
    }

    public function testCompileWithNoIsActiveCallback()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], function () {
        });

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithStringCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], 'handler');
        $rewrite->setIsActiveCallback('isactivecallback');

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }
}
