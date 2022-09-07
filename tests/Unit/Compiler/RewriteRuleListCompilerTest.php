<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Compiler\RewriteRuleListCompiler;
use ToyWpRouting\RewriteRule;

class RewriteRuleListCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompile()
    {
        $one = new RewriteRule('^oneregex$', 'index.php?var=one', 'pfx_');
        $two = new RewriteRule('^tworegex$', 'index.php?var=two', 'pfx_');

        $this->assertMatchesSnapshot((new RewriteRuleListCompiler([$one, $two]))->compile());
    }
}
