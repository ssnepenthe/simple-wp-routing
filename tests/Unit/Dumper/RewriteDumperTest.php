<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\Dumper;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use stdClass;
use SimpleWpRouting\Dumper\RewriteDumper;
use SimpleWpRouting\Support\Rewrite;

class RewriteDumperTest extends TestCase
{
    use MatchesSnapshots;

    public function testDumpWithArrayCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], ['handlerclass', 'handlermethod']);
        $rewrite->setIsActiveCallback(['isactiveclass', 'isactivemethod']);

        $this->assertMatchesSnapshot((new RewriteDumper($rewrite))->dump());
    }

    public function testDumpWithClosureCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], function () {
        });
        $rewrite->setIsActiveCallback(function () {
        });

        $this->assertMatchesSnapshot((new RewriteDumper($rewrite))->dump());
    }

    public function testDumpWithInvalidHandler()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(['GET'], '^regex$', 'index.php?pfx_var=val', ['pfx_var' => 'var'], [new stdClass(), 'methodname']);

        (new RewriteDumper($rewrite))->dump();
    }

    public function testDumpWithInvalidIsActiveCallback()
    {
        // @todo Exception message?
        $this->expectException(RuntimeException::class);

        $rewrite = new Rewrite(['GET'], '^regex$', 'index.php?var=val', ['var' => 'var'], 'handler', 'pfx_');
        $rewrite->setIsActiveCallback([new stdClass(), 'methodname']);

        (new RewriteDumper($rewrite))->dump();
    }

    public function testDumpWithNoIsActiveCallback()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], function () {
        });

        $this->assertMatchesSnapshot((new RewriteDumper($rewrite))->dump());
    }

    public function testDumpWithStringCallbacks()
    {
        $rewrite = new Rewrite(['GET', 'POST'], '^regex$', 'index.php?pfx_var=value', ['pfx_var' => 'var'], 'handler');
        $rewrite->setIsActiveCallback('isactivecallback');

        $this->assertMatchesSnapshot((new RewriteDumper($rewrite))->dump());
    }
}
