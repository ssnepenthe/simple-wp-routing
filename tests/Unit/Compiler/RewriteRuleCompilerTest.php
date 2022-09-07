<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit\Compiler;

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use ToyWpRouting\Compiler\RewriteRuleCompiler;
use ToyWpRouting\RewriteRule;

class RewriteRuleCompilerTest extends TestCase
{
    use MatchesSnapshots;

    public function testCompile()
    {
        $rule = new RewriteRule('^regex$', 'index.php?var=value', 'pfx_');

        $this->assertMatchesSnapshot((new RewriteRuleCompiler($rule))->compile());
    }
}
