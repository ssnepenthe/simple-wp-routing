<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\MethodNotAllowedResponder;
use ToyWpRouting\Orchestrator;
use ToyWpRouting\RequestContext;
use ToyWpRouting\ResponderInterface;
use ToyWpRouting\RouteCollection;

use function Brain\Monkey\Actions\expectDone;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

// @todo Test custom prefix? Test custom invoker?
class OrchestratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        setUp();
    }

    protected function tearDown(): void
    {
        tearDown();
        parent::tearDown();
    }

    public function testCacheRewrites()
    {
        $root = vfsStream::setup();

        $orchestrator = new Orchestrator();
        $orchestrator->getContainer()->setCacheDir($root->url());
        $orchestrator->cacheRewrites();

        $this->assertTrue($root->hasChild('rewrite-cache.php'));
    }

    public function testCacheRewritesWhenCacheAlreadyExists()
    {
        $this->expectException(RuntimeException::class);

        $root = vfsStream::setup();

        $orchestrator = new Orchestrator();
        $orchestrator->getContainer()->setCacheDir($root->url());
        $orchestrator->getContainer()->setCacheFile('cache.php');

        touch($root->url() . '/cache.php');

        $orchestrator->cacheRewrites();
    }

    public function testCacheRewritesWithCustomFilename()
    {
        $root = vfsStream::setup();

        $orchestrator = new Orchestrator();
        $orchestrator->getContainer()->setCacheDir($root->url());
        $orchestrator->getContainer()->setCacheFile('custom-name.php');
        $orchestrator->cacheRewrites();

        $this->assertTrue($root->hasChild('custom-name.php'));
    }

    public function testOnInit()
    {
        expectDone('toy_wp_routing.init')
            ->once()
            ->with(Mockery::type(RouteCollection::class));

        $orchestrator = new Orchestrator();
        $orchestrator->onInit();
    }

    public function testOnInitWithCachedRewrites()
    {
        $root = vfsStream::setup();

        $orchestrator = new Orchestrator();
        $orchestrator->getContainer()->setCacheDir($root->url());
        $orchestrator->cacheRewrites();

        $orchestrator->onInit();

        $this->assertSame(0, did_action('toy_wp_routing.init'));
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArray()
    {
        $orchestrator = new Orchestrator();

        $routes = $orchestrator->getContainer()->getRouteCollection();
        $routes->get('three', 'threehandler');
        $routes->get('four', 'fourhandler');

        $generatedRules = ['^three$' => 'index.php?matchedRoute=' . md5('^three$'), '^four$' => 'index.php?matchedRoute=' . md5('^four$')];
        $existingRules = ['one' => 'index.php?var=value', 'two' => 'index.php?var=value'];

        $this->assertSame(
            array_merge($generatedRules, $existingRules),
            $orchestrator->onOptionRewriteRules($existingRules)
        );
        $this->assertSame(
            array_merge($generatedRules, $existingRules),
            $orchestrator->onRewriteRulesArray($existingRules)
        );
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArrayWithDisabledRoutes()
    {
        $orchestrator = new Orchestrator();

        $routes = $orchestrator->getContainer()->getRouteCollection();
        $routes->get('three', 'threehandler');
        $routes->get('four', 'fourhandler')->when(function () {
            return false;
        });

        $generatedRules = ['^three$' => 'index.php?matchedRoute=' . md5('^three$')];
        $existingRules = ['one' => 'index.php?var=value', 'two' => 'index.php?var=value'];

        $this->assertSame(
            array_merge($generatedRules, $existingRules),
            $orchestrator->onOptionRewriteRules($existingRules)
        );
        $this->assertSame(
            array_merge($generatedRules, $existingRules),
            $orchestrator->onRewriteRulesArray($existingRules)
        );
    }

    public function testOnOptionRewriteRulesAndOnRewriteRulesArrayWithInvalidExistingRules()
    {
        $orchestrator = new Orchestrator();

        $routes = $orchestrator->getContainer()->getRouteCollection();
        $routes->get('one', 'onehandler');
        $routes->get('two', 'twohandler');

        // Not array or empty array input get returned unmodified.
        $this->assertSame(null, $orchestrator->onOptionRewriteRules(null));
        $this->assertSame(0, $orchestrator->onRewriteRulesArray(0));

        $this->assertSame([], $orchestrator->onOptionRewriteRules([]));
        $this->assertSame([], $orchestrator->onRewriteRulesArray([]));
    }

    public function testOnPreUpdateOptionRewriteRules()
    {
        $orchestrator = new Orchestrator();

        $routes = $orchestrator->getContainer()->getRouteCollection();
        $routes->get('three', 'threehandler');
        // Rules are removed even when they are not active.
        $routes->get('four', 'fourhandler')->when(function () {
            return false;
        });

        $allRules = ['^three$' => 'index.php?matchedRoute=' . md5('^three$'), '^four$' => 'index.php?matchedRoute=' . md5('^four$'), 'one' => 'index.php?var=value', 'two' => 'index.php?var=value'];
        $existingRules = ['one' => 'index.php?var=value', 'two' => 'index.php?var=value'];

        $this->assertSame($existingRules, $orchestrator->onPreUpdateOptionRewriteRules($allRules));
    }

    public function testOnPreUpdateOptionRewriteRulesWithInvalidExistingRules()
    {
        $orchestrator = new Orchestrator();

        $routes = $orchestrator->getContainer()->getRouteCollection();
        $routes->get('three', 'threehandler');
        $routes->get('four', 'fourhandler');


        // Non array or empty array values are returned un-modified.
        $this->assertSame(null, $orchestrator->onPreUpdateOptionRewriteRules(null));
        $this->assertSame([], $orchestrator->onPreUpdateOptionRewriteRules([]));
    }

    public function testOnQueryVars()
    {
        $orchestrator = new Orchestrator();

        $routes = $orchestrator->getContainer()->getRouteCollection();
        $routes->get('three', 'threehandler');
        $routes->get('four', 'fourhandler');

        $this->assertSame(['matchedRoute'], $orchestrator->onQueryVars([]));
        $this->assertSame(['matchedRoute', 'var'], $orchestrator->onQueryVars(['var']));
    }

    public function testOnQueryVarsWithInvalidExistingVars()
    {
        $orchestrator = new Orchestrator();

        $routes = $orchestrator->getContainer()->getRouteCollection();
        $routes->get('three', 'threehandler');
        $routes->get('four', 'fourhandler');

        // Non array values are returned unmodified.
        $this->assertSame(null, $orchestrator->onQueryVars(null));
    }

    public function testOnRequest()
    {
        $orchestrator = new Orchestrator();
        $orchestrator->getContainer()->getRouteCollection()->get('users/{id}', function () {
            throw new RuntimeException('This should not happen');
        });
        $input = ['var' => 'value'];

        // Input is returned unmodified.
        $this->assertSame($input, $orchestrator->onRequest($input));

        // Nothing happens with invalid input.
        $orchestrator->onRequest(false);
        $orchestrator->onRequest([]);
        $orchestrator->onRequest(['matchedRoute' => 5]);

        // Nothing happens when matchedRoute isn't a registered rewrite.
        $orchestrator->onRequest(['matchedRoute' => 'doesntmatter']);
    }

    public function testOnRequestMatchedRewrite()
    {
        $count = 0;

        $orchestrator = new Orchestrator();
        $container = $orchestrator->getContainer();
        $container->getRouteCollection()->get('users/{id}', function () use (&$count) {
            $count++;
        });
        $container->setRequestContext(new RequestContext('GET', []));

        $orchestrator->onRequest(['matchedRoute' => md5('^users/([^/]+)$')]);

        $this->assertSame(1, $count);
    }

    public function testOnRequestMatchedRewriteButInvalidMethod()
    {
        $orchestrator = new Orchestrator();
        $container = $orchestrator->getContainer();
        $container->getRouteCollection()->get('users/{id}', function () {
            throw new RuntimeException('This should not happen');
        });
        $container->setRequestContext(new RequestContext('POST', []));

        $orchestrator->onRequest(['matchedRoute' => md5('^users/([^/]+)$')]);

        $fqcn = MethodNotAllowedResponder::class;

        $this->assertNotFalse(has_filter('body_class', "{$fqcn}->onBodyClass()"));
        $this->assertNotFalse(has_filter('document_title_parts', "{$fqcn}->onDocumentTitleParts()"));
        $this->assertNotFalse(has_action('parse_query', "{$fqcn}->onParseQuery()"));
        $this->assertNotFalse(has_filter('template_include', "{$fqcn}->onTemplateInclude()"));
        $this->assertNotFalse(has_filter('wp_headers', "{$fqcn}->onWpHeaders()"));
    }

    public function testOnRequestMatchedRewriteWithResponderReturnedFromHandler()
    {
        $responder = new class () implements ResponderInterface {
            public $count = 0;
            public function respond()
            {
                $this->count++;
            }
        };
        $orchestrator = new Orchestrator();
        $container = $orchestrator->getContainer();
        $container->getRouteCollection()->get('users/{id}', function () use ($responder) {
            return $responder;
        });
        $container->setRequestContext(new RequestContext('GET', []));

        $orchestrator->onRequest(['matchedRoute' => md5('^users/([^/]+)$')]);

        $this->assertSame(1, $responder->count);
    }

    public function testOnRequestMatchedRewriteWithVariables()
    {
        $foundId = $foundFormat = null;

        $orchestrator = new Orchestrator();
        $container = $orchestrator->getContainer();
        $container->getRouteCollection()->get(
            'users/{id}/{format}',
            function ($id, $format) use (&$foundId, &$foundFormat) {
                $foundId = $id;
                $foundFormat = $format;
            }
        );
        $container->setRequestContext(new RequestContext('GET', []));

        $orchestrator->onRequest([
            'matchedRoute' => md5('^users/([^/]+)/([^/]+)$'),
            'id' => '123',
            'format' => 'json',
        ]);

        $this->assertSame('123', $foundId);
        $this->assertSame('json', $foundFormat);
    }
}
