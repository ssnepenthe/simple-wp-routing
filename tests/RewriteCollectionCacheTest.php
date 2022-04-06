<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use Closure;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\OptimizedRewrite;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionCache;

class RewriteCollectionCacheTest extends TestCase
{
    public function testDelete()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        // No errors when file does not exist.
        $cache->delete();

        touch($root->url() . '/cache.php');

        $this->assertTrue($root->hasChild('cache.php'));

        $cache->delete();

        $this->assertFalse($root->hasChild('cache.php'));
    }

    public function testExists()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $this->assertFalse($cache->exists());

        touch($root->url() . '/cache.php');

        $this->assertTrue($cache->exists());
    }

    public function testGet()
    {
        $cache = new RewriteCollectionCache(__DIR__ . '/fixtures');

        $rewriteCollection = $cache->get();

        $this->assertInstanceOf(RewriteCollection::class, $rewriteCollection);
        $this->assertCount(2, $rewriteCollection->getRewrites());

        $this->assertInstanceOf(OptimizedRewrite::class, $rewriteCollection->getRewrites()[0]);
        $this->assertNull($rewriteCollection->getRewrites()[0]->getIsActiveCallback());
        $this->assertSame(['GET'], $rewriteCollection->getRewrites()[0]->getMethods());
        $this->assertSame(
            ['/first/' => 'index.php?first=first'],
            $rewriteCollection->getRewrites()[0]->getRules()
        );

        $this->assertInstanceOf(OptimizedRewrite::class, $rewriteCollection->getRewrites()[1]);
        $this->assertSame(
            'secondisactive',
            $rewriteCollection->getRewrites()[1]->getIsActiveCallback()
        );
        $this->assertSame(['POST'], $rewriteCollection->getRewrites()[1]->getMethods());
        $this->assertSame(
            ['/second/' => 'index.php?second=second'],
            $rewriteCollection->getRewrites()[1]->getRules()
        );
    }

    public function testGetWithSerializedClosures()
    {
        $cache = new RewriteCollectionCache(
            __DIR__ . '/fixtures',
            'rewrite-cache-serialized-closures.php'
        );

        $rewriteCollection = $cache->get();
        $rewrite = $rewriteCollection->getRewrites()[0];

        $this->assertInstanceOf(Closure::class, $rewrite->getHandler());
        $this->assertInstanceOf(Closure::class, $rewrite->getIsActiveCallback());
    }

    public function testPut()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();

        $one = new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'somehandler', 'pfx_');
        $two = new Rewrite(
            ['GET', 'POST'],
            ['/second/' => ['second' => 'second'], '/third/' => ['third' => 'third']],
            'anotherhandler',
            '',
            'isActive'
        );

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);

        $cache->put($rewriteCollection);

        $this->assertTrue($root->hasChild('cache.php'));

        $this->assertSame([
            [
                'methods' => ['GET'],
                'rules' => ['/first/' => 'index.php?pfx_first=first'],
                'handler' => 'somehandler',
                'prefixedToUnprefixedQueryVariablesMap' => ['pfx_first' => 'first'],
                'queryVariables' => ['pfx_first'],
                'isActiveCallback' => null
            ],
            [
                'methods' => ['GET', 'POST'],
                'rules' => [
                    '/second/' => 'index.php?second=second',
                    '/third/' => 'index.php?third=third',
                ],
                'handler' => 'anotherhandler',
                'prefixedToUnprefixedQueryVariablesMap' => [
                    'second' => 'second',
                    'third' => 'third'
                ],
                'queryVariables' => ['second', 'third'],
                'isActiveCallback' => 'isActive',
            ],
        ], include $root->getChild('cache.php')->url());
    }

    public function testPutDirectoryDoesNotExist()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url() . '/somedir', 'cache.php');

        $rewriteCollection = new RewriteCollection();

        $cache->put($rewriteCollection);

        $this->assertTrue($root->hasChild('somedir'));
        $this->assertTrue($root->hasChild('somedir/cache.php'));
    }

    public function testPutFileAlreadyExists()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();

        $cache->put($rewriteCollection);

        $this->assertSame([], include $root->getChild('cache.php')->url());

        $rewriteCollection->add(
            new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'somehandler')
        );
        $cache->put($rewriteCollection);

        $this->assertNotSame([], include $root->getChild('cache.php')->url());
    }

    public function testPutWithClosureCallbacks()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();

        $one = new Rewrite(['GET'], ['/first/' => ['first' => 'first']], function () {
        }, 'pfx_');
        $two = new Rewrite(
            ['POST'],
            ['/second/' => ['second' => 'second']],
            'anotherhandler',
            '',
            function () {
                return true;
            }
        );

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);

        $cache->put($rewriteCollection);

        $dumped = include $root->getChild('cache.php')->url();

        $this->assertStringStartsWith(
            'C:32:"Opis\Closure\SerializableClosure"',
            $dumped[0]['handler']
        );
        $this->assertStringStartsWith(
            'C:32:"Opis\Closure\SerializableClosure"',
            $dumped[1]['isActiveCallback']
        );
    }
}
