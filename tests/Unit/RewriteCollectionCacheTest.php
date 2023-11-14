<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use Closure;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Dumper\OptimizedRewrite;
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
        // @todo Why are we using actual filesystem here instead of vfsStream?
        $cache = new RewriteCollectionCache(__DIR__ . '/../fixtures');

        $rewriteCollection = $cache->get();

        $this->assertInstanceOf(RewriteCollection::class, $rewriteCollection);

        $first = $rewriteCollection->findByRegex('^first$')['GET'];
        $second = $rewriteCollection->findByRegex('^second$')['POST'];

        $this->assertInstanceOf(OptimizedRewrite::class, $first);
        $this->assertSame('firsthandler', $first->getHandler());
        $this->assertFalse($first->hasIsActiveCallback());
        $this->assertSame(['GET', 'HEAD'], $first->getMethods());

        $this->assertInstanceOf(OptimizedRewrite::class, $second);
        $this->assertSame(
            'secondisactivecallback',
            $second->getIsActiveCallback()
        );
        $this->assertSame(['POST'], $second->getMethods());
    }

    public function testGetWithSerializedClosures()
    {
        $cache = new RewriteCollectionCache(
            __DIR__ . '/../fixtures',
            'rewrite-cache-serialized-closures.php'
        );

        $rewriteCollection = $cache->get();
        $rewrite = $rewriteCollection->findByRegex('^regex$')['GET'];

        $this->assertInstanceOf(Closure::class, $rewrite->getHandler());
        $this->assertInstanceOf(Closure::class, $rewrite->getIsActiveCallback());
    }

    public function testPut()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();

        $one = new Rewrite(['GET'], 'first', 'index.php?first=first', ['first' => 'first'], 'somehandler', 'pfx_');
        $two = new Rewrite(['GET', 'POST'], 'second', 'index.php?second=second&third=third', ['second' => 'second', 'third' => 'third'], 'anotherhandler');
        $two->setIsActiveCallback('isActive');

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);

        $cache->put($rewriteCollection);

        $this->assertTrue($root->hasChild('cache.php'));
        $this->assertInstanceOf(
            RewriteCollection::class,
            (include $root->getChild('cache.php')->url())()
        );
    }

    public function testPutDirectoryDoesNotExist()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url() . '/somedir', 'cache.php');

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->add(new Rewrite(['GET'], '^regex$', 'index.php?var=val', ['var' => 'var'], 'handler'));

        $cache->put($rewriteCollection);

        $this->assertTrue($root->hasChild('somedir'));
        $this->assertTrue($root->hasChild('somedir/cache.php'));
    }

    public function testPutFileAlreadyExists()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->add(new Rewrite(['GET'], '^regex$', 'index.php?var=val', ['var' => 'var'], 'handler'));

        $cache->put($rewriteCollection);

        $this->assertInstanceOf(
            RewriteCollection::class,
            (include $root->getChild('cache.php')->url())()
        );

        $rewriteCollection->add(new Rewrite(['GET'], 'first', 'index.php?first=first', ['first' => 'first'], 'somehandler'));
        $cache->put($rewriteCollection);

        $this->assertNotSame([], include $root->getChild('cache.php')->url());
    }

    public function testPutWithClosureCallbacks()
    {
        $root = vfsStream::setup();
        $cache = new RewriteCollectionCache($root->url(), 'cache.php');

        $rewriteCollection = new RewriteCollection();

        $one = new Rewrite(['GET'], 'first', 'index.php?pfx_first=first', ['pfx_first' => 'first'], function () {
        });
        $two = new Rewrite(['POST'], 'second', 'index.php?second=second', ['second' => 'second'], 'anotherhandler');
        $two->setIsActiveCallback(function () {
            return true;
        });

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);

        $cache->put($rewriteCollection);

        $dumped = (include $root->getChild('cache.php')->url())();
        $dumpedOne = $dumped->findByRegex('first')['GET'];
        $dumpedTwo = $dumped->findByRegex('second')['POST'];

        $this->assertInstanceOf(Closure::class, $dumpedOne->getHandler());
        $this->assertInstanceOf(Closure::class, $dumpedTwo->getIsActiveCallback());
    }
}
