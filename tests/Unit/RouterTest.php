<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use LogicException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ToyWpRouting\Router;

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
        $router = new Router();

        // Indirectly via add.
        $router->add(['GET'], 'one/{two}', 'onehandler');

        // Also groups + autoslash.
        $router->group('three', function ($router) {
            $router->add(['GET'], '{four}', 'threehandler');
        });

        [$rewriteOne, $rewriteTwo] = $router->rewriteCollection()->getRewrites();

        $this->assertSame('^(?|one/([^/]+))$', $rewriteOne->getRegex());
        $this->assertSame('index.php?two=$matches[1]&__routeType=variable', $rewriteOne->getQuery());

        $this->assertSame('^(?|three/([^/]+))$', $rewriteTwo->getRegex());
        $this->assertSame('index.php?four=$matches[1]&__routeType=variable', $rewriteTwo->getQuery());
    }

    public function testCreateWithPrefix()
    {
        $router = new Router();
        $router->setPrefix('pfx_');

        // Indirectly via get.
        $router->get('one/{two}', 'onehandler');

        // Also groups + autoslash.
        $router->group('three', function ($router) {
            $router->get('{four}', 'threehandler');
        });

        [$rewriteOne, $rewriteTwo] = $router->rewriteCollection()->getRewrites();

        $this->assertSame('^(?|one/([^/]+))$', $rewriteOne->getRegex());
        $this->assertSame('index.php?pfx_two=$matches[1]&pfx___routeType=variable', $rewriteOne->getQuery());

        $this->assertSame('^(?|three/([^/]+))$', $rewriteTwo->getRegex());
        $this->assertSame('index.php?pfx_four=$matches[1]&pfx___routeType=variable', $rewriteTwo->getQuery());
    }

    public function testHttpMethodShorthandMethods()
    {
        $router = new Router();
        $router->add(['GET', 'POST'], 'customroute', 'handler');
        $router->any('anyroute', 'handler');
        $router->delete('deleteroute', 'handler');
        $router->get('getroute', 'handler');
        $router->options('optionsroute', 'handler');
        $router->patch('patchroute', 'handler');
        $router->post('postroute', 'handler');
        $router->put('putroute', 'handler');

        $methods = array_map(fn ($rewrite) => $rewrite->getMethods(), $router->rewriteCollection()->getRewrites());

        $this->assertSame(['GET', 'POST'], $methods[0]);
        $this->assertSame(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $methods[1]);
        $this->assertSame(['DELETE'], $methods[2]);
        $this->assertSame(['GET', 'HEAD'], $methods[3]);
        $this->assertSame(['OPTIONS'], $methods[4]);
        $this->assertSame(['PATCH'], $methods[5]);
        $this->assertSame(['POST'], $methods[6]);
        $this->assertSame(['PUT'], $methods[7]);
    }

    public function testGroup()
    {
        $router = new Router();
        $router->group('one', function ($router) {
            $router->get('two', 'handler');
            $router->get('three', 'handler');

            $router->group('four', function ($router) {
                $router->get('five', 'handler');
            });
        });

        $regexes = array_map(fn ($rewrite) => $rewrite->getRegex(), $router->rewriteCollection()->getRewrites());

        $this->assertSame('^(?|one/two)$', $regexes[0]);
        $this->assertSame('^(?|one/three)$', $regexes[1]);
        $this->assertSame('^(?|one/four/five)$', $regexes[2]);
    }

    public function testInitializeThrowsWhenAlreadyInitialized()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('already initialized');

        $router = new Router();

        $router->get('irrelevant', 'handler');

        $router->initialize();
        $router->initialize();
    }

    public function testInitializeWithoutCallbackAndCacheEnabled()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('$callback must be callable');

        $root = vfsStream::setup();

        $router = new Router();
        $router->enableCache($root->url());

        $router->initialize();
    }

    public function testInitializeWithoutCallbackAndNoRoutes()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('routes must be registered before');

        $router = new Router();

        $router->initialize();
    }

    public function testInitializeWithoutCallback()
    {
        $router = new Router();

        $router->get('irrelevant', 'handler');

        $router->initialize();

        $this->assertTrue($router->rewriteCollection()->isLocked());
    }

    public function testInitializeWithCallbackAndCacheEnabledAndRewriteCollectionAlreadyInstantiated()
    {
        $this->expectException(LogicException::class);
        // $this->expectExceptionMessage('@todo');

        $root = vfsStream::setup();

        $router = new Router();
        $router->enableCache($root->url());

        $router->get('irrelevant', 'handler');

        $router->initialize(function () {});
    }

    public function testInitializeWithCallbackAndCacheEnabledAndCacheExists()
    {
        $this->expectOrchestratorInitializeMethodToBeCalled();

        $root = vfsStream::setup();

        $router = new Router();
        $router->enableCache($root->url());

        $router->initialize(function ($router) {
            $router->get('irrelevant', 'handler');
        });

        $router->rewriteCollectionCache()->put($router->rewriteCollection());

        // Sanity.
        $this->assertTrue($root->hasChild('rewrite-cache.php'));

        $router = new Router();
        $router->enableCache($root->url());

        $test = 0;
        $router->initialize(function ($router) use (&$test) {
            $test++;

            $router->get('also-irrelevant', 'this-should-not-be-called');
        });

        // If the test variable remains unchanged we know that the closure was not called.
        $this->assertSame(0, $test);
        // And if the rewrite collection is an anonymous class we know that it was loaded from cache.
        $this->assertTrue((new ReflectionClass($router->rewriteCollection()))->isAnonymous());
    }

    public function testInitializeWithCallbackAndCacheEnabledAndCacheDoesNotExist()
    {
        // Shutdown action is used to save cache.
        Actions\expectAdded('shutdown');
        $this->expectOrchestratorInitializeMethodToBeCalled();

        $root = vfsStream::setup();

        $router = new Router();
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
        // And if the rewrite collection is not an anonymous class we know that is was not loaded from cache.
        $this->assertFalse((new ReflectionClass($router->rewriteCollection()))->isAnonymous());
    }

    public function testInitializeWithCallbackAndCacheDisabled()
    {
        $this->expectOrchestratorInitializeMethodToBeCalled();

        $router = new Router();

        // Allowed to register routes before as long as cache is not enabled.
        $router->get('irrelevant', 'handler');

        $router->initialize(function ($router) {
            $router->get('also-irrelevant', 'handler');
        });

        $this->assertTrue($router->rewriteCollection()->isLocked());
    }

    private function expectOrchestratorInitializeMethodToBeCalled()
    {
        Actions\expectAdded('parse_request');

        Filters\expectAdded('option_rewrite_rules');
        Filters\expectAdded('rewrite_rules_array');
        Filters\expectAdded('pre_update_option_rewrite_rules');
        Filters\expectAdded('query_vars');
    }
}
