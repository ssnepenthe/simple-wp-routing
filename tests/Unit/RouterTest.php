<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;
use SimpleWpRouting\Router;
use SimpleWpRouting\Support\RequestContext;

class RouterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testCreate()
    {
        $router = $this->makeRouter();

        // Indirectly via add.
        $router->add(['GET'], 'one/{two}', 'onehandler');

        // Also groups + autoslash.
        $router->group('three', function ($router) {
            $router->add(['GET'], '{four}', 'threehandler');
        });

        // Multiple rewrites for route with optional segment.
        $router->get('five[/{six}]', 'fivehandler');

        [$rewriteOne, $rewriteTwo, $rewriteThree, $rewriteFour] = $router->getRewriteCollection()->getRewrites();

        $this->assertSame('^one/([^/]+)$', $rewriteOne->getRegex());
        $this->assertSame('index.php?two=$matches[1]&__routeType=variable', $rewriteOne->getQuery());
        $this->assertSame(['two' => 'two'], $rewriteOne->getQueryVariables());

        $this->assertSame('^three/([^/]+)$', $rewriteTwo->getRegex());
        $this->assertSame('index.php?four=$matches[1]&__routeType=variable', $rewriteTwo->getQuery());
        $this->assertSame(['four' => 'four'], $rewriteTwo->getQueryVariables());

        $this->assertSame('^five$', $rewriteThree->getRegex());
        $this->assertSame('index.php?__routeType=static', $rewriteThree->getQuery());
        $this->assertSame([], $rewriteThree->getQueryVariables());

        $this->assertSame('^five/([^/]+)$', $rewriteFour->getRegex());
        $this->assertSame('index.php?six=$matches[1]&__routeType=variable', $rewriteFour->getQuery());
        $this->assertSame(['six' => 'six'], $rewriteFour->getQueryVariables());
    }

    public function testCreateWithPrefix()
    {
        $router = $this->makeRouter();
        $router->setPrefix('pfx_');

        // Indirectly via get.
        $router->get('one/{two}', 'onehandler');

        // Also groups + autoslash.
        $router->group('three', function ($router) {
            $router->get('{four}', 'threehandler');
        });

        // Multiple rewrites for route with optional segment.
        $router->get('five[/{six}]', 'fivehandler');

        [$rewriteOne, $rewriteTwo, $rewriteThree, $rewriteFour] = $router->getRewriteCollection()->getRewrites();

        $this->assertSame('^one/([^/]+)$', $rewriteOne->getRegex());
        $this->assertSame('index.php?pfx_two=$matches[1]&pfx___routeType=variable', $rewriteOne->getQuery());
        $this->assertSame(['pfx_two' => 'two'], $rewriteOne->getQueryVariables());

        $this->assertSame('^three/([^/]+)$', $rewriteTwo->getRegex());
        $this->assertSame('index.php?pfx_four=$matches[1]&pfx___routeType=variable', $rewriteTwo->getQuery());
        $this->assertSame(['pfx_four' => 'four'], $rewriteTwo->getQueryVariables());

        $this->assertSame('^five$', $rewriteThree->getRegex());
        $this->assertSame('index.php?pfx___routeType=static', $rewriteThree->getQuery());
        $this->assertSame([], $rewriteThree->getQueryVariables());

        $this->assertSame('^five/([^/]+)$', $rewriteFour->getRegex());
        $this->assertSame('index.php?pfx_six=$matches[1]&pfx___routeType=variable', $rewriteFour->getQuery());
        $this->assertSame(['pfx_six' => 'six'], $rewriteFour->getQueryVariables());
    }

    public function testHttpMethodShorthandMethods()
    {
        $router = $this->makeRouter();
        $router->add(['GET', 'POST'], 'customroute', 'handler');
        $router->any('anyroute', 'handler');
        $router->delete('deleteroute', 'handler');
        $router->get('getroute', 'handler');
        $router->options('optionsroute', 'handler');
        $router->patch('patchroute', 'handler');
        $router->post('postroute', 'handler');
        $router->put('putroute', 'handler');

        $methods = array_map(fn ($rewrite) => $rewrite->getMethods(), $router->getRewriteCollection()->getRewrites());

        $this->assertSame(['GET', 'POST'], $methods[0]);
        $this->assertSame(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $methods[1]);
        $this->assertSame(['DELETE'], $methods[2]);
        $this->assertSame(['GET', 'HEAD'], $methods[3]);
        $this->assertSame(['OPTIONS'], $methods[4]);
        $this->assertSame(['PATCH'], $methods[5]);
        $this->assertSame(['POST'], $methods[6]);
        $this->assertSame(['PUT'], $methods[7]);
    }

    public function testGroupWithAutoSlashDisabled()
    {
        $router = $this->makeRouter();
        $router->disableAutoSlash();
        $router->group('one', function ($router) {
            $router->group('two', function ($router) {
                $router->get('three', 'handler');
            });
        });

        $this->assertSame('^onetwothree$', $router->getRewriteCollection()->getRewrites()[0]->getRegex());
    }

    public function testGroup()
    {
        $router = $this->makeRouter();
        $router->group('one', function ($router) {
            $router->get('two', 'handler');
            $router->get('three', 'handler');

            $router->group('four', function ($router) {
                $router->get('five', 'handler');
            });
        });

        $regexes = array_map(fn ($rewrite) => $rewrite->getRegex(), $router->getRewriteCollection()->getRewrites());

        $this->assertSame('^one/two$', $regexes[0]);
        $this->assertSame('^one/three$', $regexes[1]);
        $this->assertSame('^one/four/five$', $regexes[2]);
    }

    public function testInitializeThrowsWhenAlreadyInitialized()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('already initialized');

        $router = $this->makeRouter();

        $router->get('irrelevant', 'handler');

        $router->initialize();
        $router->initialize();
    }

    public function testInitializeWithoutCallbackAndCacheEnabled()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('$callback must be callable');

        $root = vfsStream::setup();

        $router = $this->makeRouter();
        $router->enableCache($root->url());

        $router->initialize();
    }

    public function testInitializeWithoutCallbackAndNoRoutes()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('routes must be registered before');

        $router = $this->makeRouter();

        $router->initialize();
    }

    public function testInitializeWithoutCallback()
    {
        $router = $this->makeRouter();

        $router->get('irrelevant', 'handler');

        $router->initialize();

        $this->assertTrue($router->getRewriteCollection()->isLocked());
    }

    public function testInitializeWithCallbackAndCacheEnabledAndRewriteCollectionAlreadyInstantiated()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('only be registered within $callback');

        $root = vfsStream::setup();

        $router = $this->makeRouter();
        $router->enableCache($root->url());

        $router->get('irrelevant', 'handler');

        $router->initialize(function () {});
    }

    public function testInitializeWithCallbackAndCacheEnabledAndCacheExists()
    {
        $this->expectOrchestratorInitializeMethodToBeCalled();

        $root = vfsStream::setup();

        $router = $this->makeRouter();
        $router->enableCache($root->url());

        $router->initialize(function ($router) {
            $router->get('irrelevant', 'handler');
        });

        $router->getRewriteCollectionCache()->put($router->getRewriteCollection());

        // Sanity.
        $this->assertTrue($root->hasChild('rewrite-cache.php'));

        $router = $this->makeRouter();
        $router->enableCache($root->url());

        $test = 0;
        $router->initialize(function ($router) use (&$test) {
            $test++;

            $router->get('also-irrelevant', 'this-should-not-be-called');
        });

        // If the test variable remains unchanged we know that the closure was not called.
        $this->assertSame(0, $test);
        // And if the rewrite collection is an optimized collection we know that it was loaded from cache.
        $this->assertInstanceOf(OptimizedRewriteCollection::class, $router->getRewriteCollection());
    }

    public function testInitializeWithCallbackAndCacheEnabledAndCacheDoesNotExist()
    {
        $this->expectOrchestratorInitializeMethodToBeCalled();

        $root = vfsStream::setup();

        $router = $this->makeRouter();
        $router->enableCache($root->url());

        // Sanity.
        $this->assertFalse($root->hasChild('rewrite-cache.php'));

        $test = 0;
        $router->initialize(function ($router) use (&$test) {
            $test++;

            $router->get('irrelevant', 'handler');
        });

        // If test variable is changed we know that the closure was called.
        $this->assertSame(1, $test);
        // And if the rewrite collection is not an optimized colelction we know that is was not loaded from cache.
        $this->assertNotInstanceOf(OptimizedRewriteCollection::class, $router->getRewriteCollection());
    }

    public function testInitializeWithCallbackAndCacheDisabled()
    {
        $this->expectOrchestratorInitializeMethodToBeCalled();

        $router = $this->makeRouter();

        // Allowed to register routes before as long as cache is not enabled.
        $router->get('irrelevant', 'handler');

        $router->initialize(function ($router) {
            $router->get('also-irrelevant', 'handler');
        });

        $this->assertTrue($router->getRewriteCollection()->isLocked());
    }

    private function expectOrchestratorInitializeMethodToBeCalled()
    {
        Actions\expectAdded('parse_request');

        Filters\expectAdded('option_rewrite_rules');
        Filters\expectAdded('rewrite_rules_array');
        Filters\expectAdded('pre_update_option_rewrite_rules');
        Filters\expectAdded('query_vars');
    }

    private function makeRouter(): Router
    {
        $router = new Router();
        $router->setRequestContext(new RequestContext('GET', []));

        return $router;
    }
}
