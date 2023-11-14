<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Dumper;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Dumper\RewriteCollectionDumper;
use ToyWpRouting\Support\Rewrite;
use ToyWpRouting\Support\RewriteCollection;

class RewriteCollectionDumperTest extends TestCase
{
    use MatchesSnapshots;

    public function testDump()
    {
        $rewrites = new RewriteCollection();
        $rewrite = new Rewrite(['POST'], '^regex$', 'index.php?some=var', ['some' => 'some'], function () {
        });

        $rewrites->add($rewrite);

        $this->assertMatchesSnapshot((new RewriteCollectionDumper($rewrites))->dump());
    }
}
