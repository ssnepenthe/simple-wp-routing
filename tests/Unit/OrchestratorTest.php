<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Orchestrator;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteRule;

// @todo Test custom prefix? Test custom invoker?
class OrchestratorTest extends TestCase
{
    protected $hash;
    protected $regex;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regex = 'someregex';
        $this->hash = md5($this->regex);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->regex = null;
        $this->hash = null;
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArray()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('three', 'index.php?three=value')], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('four', 'index.php?four=value')], 'fourhandler'));

        $orchestrator = new Orchestrator($rewrites);

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
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('three', 'index.php?three=value')], 'threehandler'));

        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('four', 'index.php?four=value')], 'fourhandler'))
            ->setIsActiveCallback(function () {
                return false;
            });

        $orchestrator = new Orchestrator($rewrites);

        $newRules = ['three' => 'index.php?three=value', 'four' => 'index.php?four=value'];
        $existingRules = ['one' => 'index.php?one=value', 'two' => 'index.php?two=value'];

        $expectedResult = array_merge($newRules, $existingRules);

        $this->assertSame($expectedResult, $orchestrator->onOptionRewriteRules($existingRules));
        $this->assertSame($expectedResult, $orchestrator->onRewriteRulesArray($existingRules));
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArrayWithInvalidExistingRules()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('one', 'index.php?one=value')], 'onehandler'));
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('two', 'index.php?two=value')], 'twohandler'));

        $orchestrator = new Orchestrator($rewrites);

        // Not array or empty array input get returned unmodified.
        $this->assertSame(null, $orchestrator->onOptionRewriteRules(null));
        $this->assertSame(0, $orchestrator->onRewriteRulesArray(0));

        $this->assertSame([], $orchestrator->onOptionRewriteRules([]));
        $this->assertSame([], $orchestrator->onRewriteRulesArray([]));
    }

    public function testOnPreUpdateOptionRewriteRules()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('three', 'index.php?three=value')], 'threehandler'));
        // Rules are removed even when they are not active.
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('four', 'index.php?four=value')], 'fourhandler'))
            ->setIsActiveCallback(function () {
                return false;
            });

        $orchestrator = new Orchestrator($rewrites);

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
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('three', 'index.php?three=value')], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('four', 'index.php?four=value')], 'fourhandler'));

        $orchestrator = new Orchestrator($rewrites);

        // Non array or empty array values are returned un-modified.
        $this->assertSame(null, $orchestrator->onPreUpdateOptionRewriteRules(null));
        $this->assertSame([], $orchestrator->onPreUpdateOptionRewriteRules([]));
    }

    public function testOnQueryVars()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('three', 'index.php?three=value')], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('four', 'index.php?four=value')], 'fourhandler'));

        $orchestrator = new Orchestrator($rewrites);

        $this->assertSame(['three', 'four'], $orchestrator->onQueryVars([]));
        $this->assertSame(
            ['three', 'four', 'var'],
            $orchestrator->onQueryVars(['var'])
        );
    }

    public function testOnQueryVarsWithInvalidExistingVars()
    {
        $rewrites = new RewriteCollection();
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('three', 'index.php?three=value')], 'threehandler'));
        $rewrites->add(new Rewrite(['GET'], [new RewriteRule('four', 'index.php?four=value')], 'fourhandler'));

        $orchestrator = new Orchestrator($rewrites);

        // Non array values are returned unmodified.
        $this->assertSame(null, $orchestrator->onQueryVars(null));
    }

    public function testOnRequest()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get($this->regex, 'index.php?var=value', function () {
            throw new RuntimeException('This should not happen');
        });

        $orchestrator = new Orchestrator($rewrites);

        $input = ['var' => 'value'];

        // Input is returned unmodified.
        $this->assertSame($input, $orchestrator->onRequest($input));

        // Nothing happens with invalid input.
        $orchestrator->onRequest(false);
        $orchestrator->onRequest([]);
        $orchestrator->onRequest(['matchedRule' => 5]);

        // Nothing happens when matchedRule doesn't match a registered rewrite.
        $orchestrator->onRequest(['matchedRule' => 'badhash']);
    }

    public function testOnRequestMatchedRewrite()
    {
        $count = 0;

        $rewrites = new RewriteCollection();
        $rewrites->get($this->regex, '', function () use (&$count) {
            $count++;
        });

        $orchestrator = new Orchestrator($rewrites, new RequestContext('GET', []));

        $orchestrator->onRequest(['matchedRule' => $this->hash]);

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
        $rewrites->get($this->regex, '', function () use ($responder) {
            return $responder;
        });

        $orchestrator = new Orchestrator($rewrites, new RequestContext('GET', []));

        $orchestrator->onRequest(['matchedRule' => $this->hash]);

        $this->assertSame(1, $responder->count);
    }

    public function testOnRequestMatchedRewriteWithVariables()
    {
        $foundId = $foundFormat = null;

        $rewrites = new RewriteCollection();

        $rewrites->get(
            $this->regex,
            'index.php?id=123&format=json',
            function ($vars) use (&$foundId, &$foundFormat) {
                $foundId = $vars['id'];
                $foundFormat = $vars['format'];
            }
        );

        $orchestrator = new Orchestrator($rewrites, new RequestContext('GET', []));

        $orchestrator->onRequest([
            'matchedRule' => $this->hash,
            'id' => '123',
            'format' => 'json',
        ]);

        $this->assertSame('123', $foundId);
        $this->assertSame('json', $foundFormat);
    }
}
