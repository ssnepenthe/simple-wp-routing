<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use SimpleWpRouting\InvocationStrategy\DefaultInvocationStrategy;
use SimpleWpRouting\CallableResolver\DefaultCallableResolver;
use SimpleWpRouting\Support\Orchestrator;
use SimpleWpRouting\Support\RequestContext;
use SimpleWpRouting\Responder\ResponderInterface;
use SimpleWpRouting\Support\Rewrite;
use SimpleWpRouting\Support\RewriteCollection;

class OrchestratorTest extends TestCase
{
    protected $regex;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regex = 'someregex';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->regex = null;
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArray()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], 'three', 'index.php?three=value', ['three' => 'three'], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], 'four', 'index.php?four=value', ['four' => 'four'], 'fourhandler'));

        $orchestrator = $this->createOrchestrator($rewrites);

        $newRules = [
            'three' => 'index.php?three=value',
            'four' => 'index.php?four=value',
        ];
        $existingRules = ['one' => 'index.php?one=value', 'two' => 'index.php?two=value'];

        $expectedResult = array_merge($newRules, $existingRules);

        $this->assertSame($expectedResult, $orchestrator->onOptionRewriteRules($existingRules));
        $this->assertSame($expectedResult, $orchestrator->onRewriteRulesArray($existingRules));
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArrayWithDisabledRoutes()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], 'three', 'index.php?three=value', ['three' => 'three'], 'threehandler'));

        $rewrites->add(new Rewrite(['GET'], 'four', 'index.php?four=value', ['four' => 'four'], 'fourhandler'))
            ->setIsActiveCallback(function () {
                return false;
            });

        $orchestrator = $this->createOrchestrator($rewrites);

        $newRules = ['three' => 'index.php?three=value', 'four' => 'index.php?four=value'];
        $existingRules = ['one' => 'index.php?one=value', 'two' => 'index.php?two=value'];

        $expectedResult = array_merge($newRules, $existingRules);

        $this->assertSame($expectedResult, $orchestrator->onOptionRewriteRules($existingRules));
        $this->assertSame($expectedResult, $orchestrator->onRewriteRulesArray($existingRules));
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArrayWithInvalidExistingRules()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], 'one', 'index.php?one=value', ['one' => 'one'], 'onehandler'));
        $rewrites->add(new Rewrite(['GET'], 'two', 'index.php?two=value', ['two' => 'two'], 'twohandler'));

        $orchestrator = $this->createOrchestrator($rewrites);

        // Not array or empty array input get returned unmodified.
        $this->assertSame(null, $orchestrator->onOptionRewriteRules(null));
        $this->assertSame(0, $orchestrator->onRewriteRulesArray(0));

        $this->assertSame([], $orchestrator->onOptionRewriteRules([]));
        $this->assertSame([], $orchestrator->onRewriteRulesArray([]));
    }

    public function testOnPreUpdateOptionRewriteRules()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], 'three', 'index.php?three=value', ['three' => 'three'], 'threehandler'));
        // Rules are removed even when they are not active.
        $rewrites->add(new Rewrite(['GET'], 'four', 'index.php?four=value', ['four' => 'four'], 'fourhandler'))
            ->setIsActiveCallback(function () {
                return false;
            });

        $orchestrator = $this->createOrchestrator($rewrites);

        $allRules = [
            'three' => 'index.php?three=value',
            'four' => 'index.php?four=value',
            'one' => 'index.php?one=value',
            'two' => 'index.php?two=value',
        ];
        $existingRules = ['one' => 'index.php?one=value', 'two' => 'index.php?two=value'];

        $this->assertSame($existingRules, $orchestrator->onPreUpdateOptionRewriteRules($allRules));
    }

    public function testOnPreUpdateOptionRewriteRulesWithInvalidExistingRules()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], 'three', 'index.php?three=value', ['three' => 'three'], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], 'four', 'index.php?four=value', ['four' => 'four'], 'fourhandler'));

        $orchestrator = $this->createOrchestrator($rewrites);

        // Non array or empty array values are returned un-modified.
        $this->assertSame(null, $orchestrator->onPreUpdateOptionRewriteRules(null));
        $this->assertSame([], $orchestrator->onPreUpdateOptionRewriteRules([]));
    }

    public function testOnQueryVars()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], 'three', 'index.php?three=value', ['three' => 'three'], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], 'four', 'index.php?four=value', ['four' => 'four'], 'fourhandler'));

        $orchestrator = $this->createOrchestrator($rewrites);

        $this->assertSame(['three', 'four'], $orchestrator->onQueryVars([]));
        $this->assertSame(
            ['three', 'four', 'var'],
            $orchestrator->onQueryVars(['var'])
        );
    }

    public function testOnQueryVarsWithInvalidExistingVars()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], 'three', 'index.php?three=value', ['three' => 'three'], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], 'four', 'index.php?four=value', ['four' => 'four'], 'fourhandler'));

        $orchestrator = $this->createOrchestrator($rewrites);

        // Non array values are returned unmodified.
        $this->assertSame(null, $orchestrator->onQueryVars(null));
    }

    public function testOnRequestMatchedRewrite()
    {
        $count = 0;

        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET', 'HEAD'], $this->regex, 'var=val', ['var' => 'var'], function () use (&$count) {
            $count++;
        }));

        $orchestrator = $this->createOrchestrator($rewrites);

        $orchestrator->onParseRequest($this->createWpDouble($this->regex, ['var' => 'val']));

        $this->assertSame(1, $count);
    }

    public function testOnRequestMatchedRewriteWithResponderReturnedFromHandler()
    {
        $responder = new class () implements ResponderInterface {
            public $count = 0;
            public function respond(): void
            {
                $this->count++;
            }
        };

        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET', 'HEAD'], $this->regex, 'var=val', ['var' => 'var'], function () use ($responder) {
            return $responder;
        }));

        $orchestrator = $this->createOrchestrator($rewrites);

        $orchestrator->onParseRequest($this->createWpDouble($this->regex, ['var' => 'val']));

        $this->assertSame(1, $responder->count);
    }

    public function testOnRequestMatchedRewriteWithVariables()
    {
        $foundId = $foundFormat = null;

        $rewrites = new RewriteCollection();
        $rewrites->add(
            new Rewrite(['GET', 'HEAD'], $this->regex, 'index.php?id=123&format=json', ['id' => 'id', 'format' => 'format'], function ($vars) use (&$foundId, &$foundFormat) {
                $foundId = $vars['id'];
                $foundFormat = $vars['format'];
            })
        );

        $orchestrator = $this->createOrchestrator($rewrites);

        $orchestrator->onParseRequest($this->createWpDouble($this->regex, [
            'id' => '123',
            'format' => 'json',
        ]));

        $this->assertSame('123', $foundId);
        $this->assertSame('json', $foundFormat);
    }

    private function createOrchestrator(RewriteCollection $rewrites): Orchestrator
    {
        return new Orchestrator(
            $rewrites,
            new DefaultInvocationStrategy(),
            new DefaultCallableResolver(),
            new RequestContext('GET', [])
        );
    }

    private function createWpDouble(string $matchedRule, array $queryVars = []): object
    {
        return (object) [
            'matched_rule' => $matchedRule,
            'query_vars' => $queryVars,
        ];
    }
}
