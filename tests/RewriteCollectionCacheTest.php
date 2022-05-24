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
        $this->assertSame(['GET'], $rewrites[0]->getMethods());
        $this->assertSame(['first' => 'first', 'matchedRule' => 'matchedRule'], $rewrites[0]->getPrefixedToUnprefixedQueryVariablesMap());
        $this->assertSame(['first', 'matchedRule'], $rewrites[0]->getQueryVariables());
        $this->assertSame(
            ['first' => 'index.php?first=first&matchedRule=8b04d5e3775d298e78455efc5ca404d5'],
            $rewrites[0]->getRewriteRules()
        );
        // @todo
        // $this->assertSame(
        //     [],
        //     $rewrites[0]->getRules()
        // );

        $this->assertInstanceOf(OptimizedRewrite::class, $rewrites[1]);
        $this->assertSame(
            'secondisactive',
            $rewrites[1]->getIsActiveCallback()
        );
        $this->assertSame(['POST'], $rewrites[1]->getMethods());
        $this->assertSame(
            ['second' => 'index.php?second=second&matchedRule=a9f0e61a137d86aa9db53465e0801612'],
            $rewrites[1]->getRewriteRules()
        );
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

        $rewriteCollection = new RewriteCollection();

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

        $this->assertSame([
            [
                'methods' => ['GET'],
                'rewriteRules' => ['first' => 'index.php?pfx_first=first&pfx_matchedRule=8b04d5e3775d298e78455efc5ca404d5'],
                'rules' => [
                    [
                        'hash' => '8b04d5e3775d298e78455efc5ca404d5',
                        'prefixedQueryArray' => ['pfx_first' => 'first', 'pfx_matchedRule' => '8b04d5e3775d298e78455efc5ca404d5'],
                        'query' => 'index.php?pfx_first=first&pfx_matchedRule=8b04d5e3775d298e78455efc5ca404d5',
                        'queryArray' => ['first' => 'first', 'matchedRule' => '8b04d5e3775d298e78455efc5ca404d5'],
                        'regex' => 'first',
                    ]
                ],
                'handler' => 'somehandler',
                'prefixedToUnprefixedQueryVariablesMap' => ['pfx_first' => 'first', 'pfx_matchedRule' => 'matchedRule'],
                'queryVariables' => ['pfx_first', 'pfx_matchedRule'],
                'isActiveCallback' => null
            ],
            [
                'methods' => ['GET', 'POST'],
                'rewriteRules' => [
                    'second' => 'index.php?second=second&matchedRule=a9f0e61a137d86aa9db53465e0801612',
                    'third' => 'index.php?third=third&matchedRule=dd5c8bf51558ffcbe5007071908e9524',
                ],
                'rules' => [
                    [
                        'hash' => 'a9f0e61a137d86aa9db53465e0801612',
                        'prefixedQueryArray' => ['second' => 'second', 'matchedRule' => 'a9f0e61a137d86aa9db53465e0801612'],
                        'query' => 'index.php?second=second&matchedRule=a9f0e61a137d86aa9db53465e0801612',
                        'queryArray' => ['second' => 'second', 'matchedRule' => 'a9f0e61a137d86aa9db53465e0801612'],
                        'regex' => 'second',
                    ],
                    [
                        'hash' => 'dd5c8bf51558ffcbe5007071908e9524',
                        'prefixedQueryArray' => ['third' => 'third', 'matchedRule' => 'dd5c8bf51558ffcbe5007071908e9524'],
                        'query' => 'index.php?third=third&matchedRule=dd5c8bf51558ffcbe5007071908e9524',
                        'queryArray' => ['third' => 'third', 'matchedRule' => 'dd5c8bf51558ffcbe5007071908e9524'],
                        'regex' => 'third',
                    ],
                ],
                'handler' => 'anotherhandler',
                'prefixedToUnprefixedQueryVariablesMap' => [
                    'second' => 'second',
                    'matchedRule' => 'matchedRule',
                    'third' => 'third'
                ],
                'queryVariables' => ['second', 'matchedRule', 'third'],
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
