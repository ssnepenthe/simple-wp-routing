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
        $rewrite = new Rewrite(
            ['GET', 'POST'],
            [(new RewriteRule('^regex$', 'index.php?var=value', 'pfx_'))->setRequiredQueryVariables(['pfx_var'])],
            ['handlerclass', 'handlermethod']
        );
        $rewrite->setIsActiveCallback(['isactiveclass', 'isactivemethod']);

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithClosureCallbacks()
    {
        $rewrite = new Rewrite(
            ['GET', 'POST'],
            [(new RewriteRule('^regex$', 'index.php?var=value', 'pfx_'))->setRequiredQueryVariables(['pfx_var'])],
            function () {
            }
        );
        $rewrite->setIsActiveCallback(function () {
        });

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithInvalidHandler()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(
            ['GET'],
            [(new RewriteRule('^regex$', 'index.php?var=val', 'pfx_'))->setRequiredQueryVariables(['pfx_var'])],
            [new stdClass(), 'methodname']
        );

        (new RewriteCompiler($rewrite))->compile();
    }

    public function testCompileWithInvalidIsActiveCallback()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(
            ['GET'],
            [(new RewriteRule('^regex$', 'index.php?var=val', 'pfx_'))->setRequiredQueryVariables(['pfx_var'])],
            'handler'
        );
        $rewrite->setIsActiveCallback([new stdClass(), 'methodname']);

        (new RewriteCompiler($rewrite))->compile();
    }

    public function testCompileWithNoIsActiveCallback()
    {
        $rewrite = new Rewrite(
            ['GET', 'POST'],
            [(new RewriteRule('^regex$', 'index.php?var=value', 'pfx_'))->setRequiredQueryVariables(['pfx_var'])],
            function () {
            }
        );

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }

    public function testCompileWithStringCallbacks()
    {
        $rewrite = new Rewrite(
            ['GET', 'POST'],
            [(new RewriteRule('^regex$', 'index.php?var=value', 'pfx_'))->setRequiredQueryVariables(['pfx_var'])],
            'handler'
        );
        $rewrite->setIsActiveCallback('isactivecallback');

        $this->assertMatchesSnapshot((new RewriteCompiler($rewrite))->compile());
    }
}
