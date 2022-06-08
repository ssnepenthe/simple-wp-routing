<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\DefaultInvocationStrategy;
use ToyWpRouting\MethodNotAllowedResponder;
use ToyWpRouting\Orchestrator;
use ToyWpRouting\RequestContext;
use ToyWpRouting\ResponderInterface;
use ToyWpRouting\RewriteCollection;

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

// @todo Test custom prefix? Test custom invoker?
class OrchestratorTest extends TestCase
{
    protected $hash;
    protected $regex;

    protected function setUp(): void
    {
        parent::setUp();
        setUp();

        $this->regex = 'someregex';
        $this->hash = md5($this->regex);
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();

        $this->regex = null;
        $this->hash = null;
    }

    public function testDefaults()
    {
        $orchestrator = new Orchestrator(new RewriteCollection());

        $this->assertInstanceOf(
            DefaultInvocationStrategy::class,
            $orchestrator->getInvocationStrategy()
        );
    }

    public function testGetActiveRewriteCollection()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get('someregex', 'index.php?var=value', 'somehandler');
        $rewrites->get(
            'anotherregex',
            'index.php?anothervar=anothervalue',
            'anotherhandler'
        )->setIsActiveCallback(function () {
            return false;
        });

        $orchestrator = new Orchestrator($rewrites);

        $active = $orchestrator->getActiveRewriteCollection();
        $activeHash = md5('someregex');

        $this->assertInstanceOf(RewriteCollection::class, $active);
        $this->assertCount(1, $active->getRewrites());
        $this->assertSame(
            ['someregex' => "index.php?var=value&matchedRule={$activeHash}"],
            $active->getRewrites()->current()->getRewriteRules()
        );

        // Result is cached.
        $this->assertSame($active, $orchestrator->getActiveRewriteCollection());
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArray()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get('three', 'index.php?three=value', 'threehandler');
        $rewrites->get('four', 'index.php?four=value', 'fourhandler');

        $orchestrator = new Orchestrator($rewrites);

        $threeHash = md5('three');
        $fourHash = md5('four');

        $newRules = [
            'three' => "index.php?three=value&matchedRule={$threeHash}",
            'four' => "index.php?four=value&matchedRule={$fourHash}",
        ];
        $existingRules = ['one' => 'index.php?one=value', 'two' => 'index.php?two=value'];

        $expectedResult = array_merge($newRules, $existingRules);

        $this->assertSame($expectedResult, $orchestrator->onOptionRewriteRules($existingRules));
        $this->assertSame($expectedResult, $orchestrator->onRewriteRulesArray($existingRules));
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArrayWithDisabledRoutes()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get('three', 'index.php?three=value', 'threehandler');

        $rewrites->get('four', 'index.php?four=value', 'fourhandler')
            ->setIsActiveCallback(function () {
                return false;
            });

        $orchestrator = new Orchestrator($rewrites);

        $threeHash = md5('three');
        $newRules = ['three' => "index.php?three=value&matchedRule={$threeHash}"];
        $existingRules = ['one' => 'index.php?one=value', 'two' => 'index.php?two=value'];

        $expectedResult = array_merge($newRules, $existingRules);

        $this->assertSame($expectedResult, $orchestrator->onOptionRewriteRules($existingRules));
        $this->assertSame($expectedResult, $orchestrator->onRewriteRulesArray($existingRules));
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArrayWithInvalidExistingRules()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get('one', 'index.php?one=value', 'onehandler');
        $rewrites->get('two', 'index.php?two=value', 'twohandler');

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
        $rewrites->get('three', 'index.php?three=value', 'threehandler');
        // Rules are removed even when they are not active.
        $rewrites->get('four', 'index.php?four=value', 'fourhandler')
            ->setIsActiveCallback(function () {
                return false;
            });

        $orchestrator = new Orchestrator($rewrites);

        $threeHash = md5('three');
        $fourHash = md5('four');

        $allRules = [
            'three' => "index.php?three=value&matchedRule={$threeHash}",
            'four' => "index.php?four=value&matchedRule={$fourHash}",
            'one' => 'index.php?one=value',
            'two' => 'index.php?two=value',
        ];
        $existingRules = ['one' => 'index.php?one=value', 'two' => 'index.php?two=value'];

        $this->assertSame($existingRules, $orchestrator->onPreUpdateOptionRewriteRules($allRules));
    }

    public function testOnPreUpdateOptionRewriteRulesWithInvalidExistingRules()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get('three', 'index.php?three=value', 'threehandler');
        $rewrites->get('four', 'index.php?four=value', 'fourhandler');

        $orchestrator = new Orchestrator($rewrites);

        // Non array or empty array values are returned un-modified.
        $this->assertSame(null, $orchestrator->onPreUpdateOptionRewriteRules(null));
        $this->assertSame([], $orchestrator->onPreUpdateOptionRewriteRules([]));
    }

    public function testOnQueryVars()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get('three', 'index.php?three=value', 'threehandler');
        $rewrites->get('four', 'index.php?four=value', 'fourhandler');

        $orchestrator = new Orchestrator($rewrites);

        $this->assertSame(['three', 'matchedRule', 'four'], $orchestrator->onQueryVars([]));
        $this->assertSame(
            ['three', 'matchedRule', 'four', 'var'],
            $orchestrator->onQueryVars(['var'])
        );
    }

    public function testOnQueryVarsWithInvalidExistingVars()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get('three', 'index.php?three=value', 'threehandler');
        $rewrites->get('four', 'index.php?four=value', 'fourhandler');

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
        $rewrites->get($this->regex, 'index.php?var=value', function () use (&$count) {
            $count++;
        });

        $orchestrator = new Orchestrator($rewrites, null, new RequestContext('GET', []));

        $orchestrator->onRequest(['matchedRule' => $this->hash]);

        $this->assertSame(1, $count);
    }

    public function testOnRequestMatchedRewriteButNotMatchedMethod()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get($this->regex, 'index.php?var=value', function () {
            throw new RuntimeException('This should not happen');
        });

        $orchestrator = new Orchestrator($rewrites, null, new RequestContext('POST', []));

        $orchestrator->onRequest(['matchedRule' => $this->hash]);

        $fqcn = MethodNotAllowedResponder::class;

        $this->assertNotFalse(has_filter('body_class', "{$fqcn}->onBodyClass()"));
        $this->assertNotFalse(
            has_filter('document_title_parts', "{$fqcn}->onDocumentTitleParts()")
        );
        $this->assertNotFalse(has_action('parse_query', "{$fqcn}->onParseQuery()"));
        $this->assertNotFalse(has_filter('template_include', "{$fqcn}->onTemplateInclude()"));
        $this->assertNotFalse(has_filter('wp_headers', "{$fqcn}->onWpHeaders()"));
    }

    public function testOnRequestMatchedRewriteInvalidRequestMethodOverride()
    {
        $rewrites = new RewriteCollection();
        $rewrites->get($this->regex, 'index.php?var=value', function () {
            throw new RuntimeException('This should not happen');
        });

        $orchestrator = new Orchestrator($rewrites, null, new RequestContext(
            'POST',
            ['X-HTTP-METHOD-OVERRIDE' => 'BADMETHOD']
        ));

        $orchestrator->onRequest(['matchedRule' => $this->hash]);
        $fqcn = MethodNotAllowedResponder::class;

        $this->assertFalse(has_filter('body_class', "{$fqcn}->onBodyClass()"));
        $this->assertFalse(
            has_filter('document_title_parts', "{$fqcn}->onDocumentTitleParts()")
        );
        $this->assertFalse(has_action('parse_query', "{$fqcn}->onParseQuery()"));
        $this->assertFalse(has_filter('template_include', "{$fqcn}->onTemplateInclude()"));
        $this->assertFalse(has_filter('wp_headers', "{$fqcn}->onWpHeaders()"));
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
        $rewrites->get($this->regex, 'index.php?var=value', function () use ($responder) {
            return $responder;
        });

        $orchestrator = new Orchestrator($rewrites, null, new RequestContext('GET', []));

        $orchestrator->onRequest(['matchedRule' => $this->hash]);

        $this->assertSame(1, $responder->count);
    }

    public function testOnRequestMatchedRewriteWithVariables()
    {
        // @todo Test with invoker backed strategy?
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

        $orchestrator = new Orchestrator($rewrites, null, new RequestContext('GET', []));

        $orchestrator->onRequest([
            'matchedRule' => $this->hash,
            'id' => '123',
            'format' => 'json',
        ]);

        $this->assertSame('123', $foundId);
        $this->assertSame('json', $foundFormat);
    }
}
