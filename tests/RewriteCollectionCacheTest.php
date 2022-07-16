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
use ToyWpRouting\RewriteRule;

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

        $rewrites = iterator_to_array($rewriteCollection->getRewrites());

        $this->assertInstanceOf(OptimizedRewrite::class, $rewrites[0]);
        $this->assertSame('firsthandler', $rewrites[0]->getHandler());
        $this->assertNull($rewrites[0]->getIsActiveCallback());
        $this->assertSame(['GET', 'HEAD'], $rewrites[0]->getMethods());
        // @todo
        // $this->assertSame(
        //     [],
        //     $rewrites[0]->getRules()
        // );

        $this->assertInstanceOf(OptimizedRewrite::class, $rewrites[1]);
        $this->assertSame(
            'secondisactivecallback',
            $rewrites[1]->getIsActiveCallback()
        );
        $this->assertSame(['POST'], $rewrites[1]->getMethods());
    }

    public function testGetWithSerializedClosures()
    {
        $cache = new RewriteCollectionCache(
            __DIR__ . '/fixtures',
            'rewrite-cache-serialized-closures.php'
        );

        $rewriteCollection = $cache->get();
        $rewrite = $rewriteCollection->getRewrites()->current();

        $this->assertInstanceOf(Closure::class, $rewrite->getHandler());
        $this->assertInstanceOf(Closure::class, $rewrite->getIsActiveCallback());
    }

    public function testPut()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection('pfx_');

        $one = new Rewrite(
            ['GET'],
            [new RewriteRule('first', 'index.php?first=first', 'pfx_')],
            'somehandler'
        );
        $two = new Rewrite(
            ['GET', 'POST'],
            [
                new RewriteRule('second', 'index.php?second=second'),
                new RewriteRule('third', 'index.php?third=third'),
            ],
            'anotherhandler'
        );
        $two->setIsActiveCallback('isActive');

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);

        $cache->put($rewriteCollection);

        $this->assertTrue($root->hasChild('cache.php'));
        $this->assertInstanceOf(
            RewriteCollection::class,
            include $root->getChild('cache.php')->url()
        );
    }

    public function testPutDirectoryDoesNotExist()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url() . '/somedir', 'cache.php');

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->get('^regex$', 'index.php?var=val', 'handler');

        $cache->put($rewriteCollection);

        $this->assertTrue($root->hasChild('somedir'));
        $this->assertTrue($root->hasChild('somedir/cache.php'));
    }

    public function testPutFileAlreadyExists()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->get('^regex$', 'index.php?var=val', 'handler');

        $cache->put($rewriteCollection);

        $this->assertInstanceOf(
            RewriteCollection::class,
            include $root->getChild('cache.php')->url()
        );

        $rewriteCollection->add(
            new Rewrite(['GET'], [new RewriteRule('first', 'index.php?first=first')], 'somehandler')
        );
        $cache->put($rewriteCollection);

        $this->assertNotSame([], include $root->getChild('cache.php')->url());
    }

    public function testPutWithClosureCallbacks()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();

        $one = new Rewrite(
            ['GET'],
            [new RewriteRule('first', 'index.php?first=first', 'pfx_')],
            function () {
            }
        );
        $two = new Rewrite(
            ['POST'],
            [new RewriteRule('second', 'index.php?second=second')],
            'anotherhandler'
        );
        $two->setIsActiveCallback(function () {
            return true;
        });

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);

        $cache->put($rewriteCollection);

        $dumped = include $root->getChild('cache.php')->url();
        $dumpedRewrites = iterator_to_array($dumped->getRewrites());

        $this->assertInstanceOf(Closure::class, $dumpedRewrites[0]->getHandler());
        $this->assertInstanceOf(Closure::class, $dumpedRewrites[1]->getIsActiveCallback());
    }
}
