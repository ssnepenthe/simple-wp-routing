<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Dumper;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Dumper\RewriteListDumper;
use ToyWpRouting\Rewrite;

class RewriteListDumperTest extends TestCase
{
    use MatchesSnapshots;

    public function testDump()
    {
        $one = new Rewrite(['GET', 'HEAD'], '^getregex$', 'index.php?pfx_var=get', ['pfx_var' => 'var'], function () {
        });
        $one->setIsActiveCallback(function () {
        });

        $two = new Rewrite(['POST'], '^postregex$', 'index.php?pfx_var=post', ['pfx_var' => 'var'], function () {
        });

        $this->assertMatchesSnapshot((new RewriteListDumper([$one, $two]))->dump());
    }
}
