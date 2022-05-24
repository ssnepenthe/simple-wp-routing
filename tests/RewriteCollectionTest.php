<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteInterface;
use ToyWpRouting\RewriteRule;

class RewriteCollectionTest extends TestCase
{
    public function testAdd()
    {
        $rewriteCollection = new RewriteCollection();
        $rewrite = new Rewrite(
            ['GET'],
            [new RewriteRule('someregex', 'index.php?var=value')],
            'somehandler'
        );

        $rewriteCollection->add($rewrite);

        $this->assertSame([$rewrite], iterator_to_array($rewriteCollection->getRewrites()));
    }

    public function testAddDuplicateRewrites()
    {
        $rewriteCollection = new RewriteCollection();
        $rule = new RewriteRule('someregex', 'index.php?var=value');
        $rewrite = new Rewrite(['GET'], [$rule], 'somehandler');

        $rewriteCollection->add($rewrite);
        $rewriteCollection->add($rewrite);

        // Rewrites are only added once.
        $this->assertCount(1, $rewriteCollection->getRewrites());

        // But rewrite rules and query variables are unique.
        $this->assertSame([
            'someregex' => "index.php?var=value&matchedRule={$rule->getHash()}"
        ], $rewriteCollection->getRewriteRules());

        // 'matchedRule' var is automatically added.
        $this->assertSame([
            'var' => 'var',
            'matchedRule' => 'matchedRule',
        ], $rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap());
        $this->assertSame(['var', 'matchedRule'], $rewriteCollection->getQueryVariables());
    }

    public function testAddWhenLocked()
    {
        $this->expectException(RuntimeException::class);

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->lock();

        $rewriteCollection->add(
            new Rewrite(
                ['GET'],
                [new RewriteRule('someregex', 'index.php?var=value')],
                'somehandler'
            )
        );
    }

    public function testAddWithPrefix()
    {
        // Prefix should not be applied to rewrites via add method, only via create method.
        $collection = new RewriteCollection('pfx_');
        $rule = new RewriteRule('someregex', 'index.php?var=value');
        $collection->add(new Rewrite(['GET'], [$rule], 'somehandler'));

        $this->assertSame([
            'someregex' => "index.php?var=value&matchedRule={$rule->getHash()}"
        ], $collection->getRewriteRules());
    }

    public function testFilter()
    {
        $rewriteCollection = new RewriteCollection();

        $one = new Rewrite(
            ['GET'],
            [new RewriteRule('first', 'index.php?first=first')],
            'somehandler'
        );
        $two = new Rewrite(
            ['POST'],
            [new RewriteRule('second', 'index.php?second=second')],
            'somehandler'
        );
        $three = new Rewrite(
            ['GET'],
            [new RewriteRule('third', 'index.php?third=third')],
            'somehandler'
        );

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);
        $rewriteCollection->add($three);

        $filtered = $rewriteCollection->filter(function (RewriteInterface $rewrite) {
            return ['GET'] === $rewrite->getMethods();
        });

        $this->assertInstanceOf(RewriteCollection::class, $filtered);
        $this->assertNotSame($rewriteCollection, $filtered);

        $this->assertSame([$one, $three], iterator_to_array($filtered->getRewrites()));
    }

    public function testFilterWhenLocked()
    {
        $rewriteCollection = new RewriteCollection();

        $rewriteCollection->add(
            new Rewrite(['GET'], [new RewriteRule('first', 'index.php?first=first')], 'somehandler')
        );

        $this->assertFalse($rewriteCollection->filter(function () {
            return true;
        })->isLocked());

        $rewriteCollection->lock();

        $this->assertTrue($rewriteCollection->filter(function () {
            return true;
        })->isLocked());
    }

    public function testGetRewritesByRegexHash()
    {
        $rewriteCollection = new RewriteCollection();

        $hash = md5('someregex');

        // Multiple methods, single rule.
        $one = new Rewrite(
            ['GET', 'POST'],
            [new RewriteRule('someregex', 'index.php?var=value')],
            'somehandler'
        );

        // Same regex, different method.
        $two = new Rewrite(
            ['PUT'],
            [new RewriteRule('someregex', 'index.php?var=value')],
            'somehandler'
        );

        // Single method, multiple rules - one regex is the same as previous.
        $three = new Rewrite(
            ['DELETE'],
            [
                new RewriteRule('someregex', 'index.php?var=value'),
                new RewriteRule('anotherregex', 'index.php?anothervar=anothervalue'),
            ],
            'somehandler'
        );

        // Different rule, same method as previous.
        $four = new Rewrite(
            ['GET'],
            [new RewriteRule('anotherregex', 'index.php?variable=value')],
            'somehandler'
        );

        $rewriteCollection->add($one);
        $rewriteCollection->add($two);
        $rewriteCollection->add($three);
        $rewriteCollection->add($four);

        $this->assertSame(
            ['GET' => $one, 'POST' => $one, 'PUT' => $two, 'DELETE' => $three],
            $rewriteCollection->getRewritesByRegexHash($hash)
        );
    }

    public function testGetRewritesByRegexHashNotFound()
    {
        $rewriteCollection = new RewriteCollection();

        $hash = md5('anotherregex');

        $rewriteCollection->add(
            new Rewrite(
                ['GET'],
                [new RewriteRule('someregex', 'index.php?var=value')],
                'somehandler'
            )
        );

        $this->assertSame([], $rewriteCollection->getRewritesByRegexHash($hash));
    }

    public function testGetters()
    {
        $one = new Rewrite(
            ['GET', 'HEAD'],
            [$rOne = new RewriteRule('first', 'index.php?first=first')],
            'somehandler'
        );
        $two = new Rewrite(
            ['POST'],
            [$rTwo = new RewriteRule('second', 'index.php?second=second')],
            'somehandler'
        );
        $three = new Rewrite(
            ['POST'],
            [
                $rThree = new RewriteRule('third', 'index.php?third=third'),
                $rFour = new RewriteRule('fourth', 'index.php?fourth=fourth'),
            ],
            'somehandler'
        );

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->add($one);
        $rewriteCollection->add($two);
        $rewriteCollection->add($three);

        $this->assertSame([
            'first' => 'first',
            'matchedRule' => 'matchedRule',
            'second' => 'second',
            'third' => 'third',
            'fourth' => 'fourth',
        ], $rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap());
        $this->assertSame(
            ['first', 'matchedRule', 'second', 'third', 'fourth'],
            $rewriteCollection->getQueryVariables()
        );
        $this->assertSame([
            'first' => "index.php?first=first&matchedRule={$rOne->getHash()}",
            'second' => "index.php?second=second&matchedRule={$rTwo->getHash()}",
            'third' => "index.php?third=third&matchedRule={$rThree->getHash()}",
            'fourth' => "index.php?fourth=fourth&matchedRule={$rFour->getHash()}",
        ], $rewriteCollection->getRewriteRules());
        $this->assertSame(
            [$one, $two, $three],
            iterator_to_array($rewriteCollection->getRewrites())
        );
    }

    public function testGettersWithPrefixedRewrites()
    {
        $prefix = 'pfx_';

        $one = new Rewrite(
            ['GET', 'HEAD'],
            [$rOne = new RewriteRule('first', 'index.php?first=first', $prefix)],
            'somehandler'
        );
        $two = new Rewrite(
            ['POST'],
            [$rTwo = new RewriteRule('second', 'index.php?second=second', $prefix)],
            'somehandler'
        );
        $three = new Rewrite(
            ['POST'],
            [
                $rThree = new RewriteRule('third', 'index.php?third=third', $prefix),
                $rFour = new RewriteRule('fourth', 'index.php?fourth=fourth', $prefix),
            ],
            'somehandler'
        );

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->add($one);
        $rewriteCollection->add($two);
        $rewriteCollection->add($three);

        $this->assertSame(
            [
            'pfx_first' => 'first',
            'pfx_matchedRule' => 'matchedRule',
            'pfx_second' => 'second',
            'pfx_third' => 'third',
            'pfx_fourth' => 'fourth',
        ],
            $rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap()
        );
        $this->assertSame(
            ['pfx_first', 'pfx_matchedRule', 'pfx_second', 'pfx_third', 'pfx_fourth'],
            $rewriteCollection->getQueryVariables()
        );
        $this->assertSame([
            'first' => "index.php?pfx_first=first&pfx_matchedRule={$rOne->getHash()}",
            'second' => "index.php?pfx_second=second&pfx_matchedRule={$rTwo->getHash()}",
            'third' => "index.php?pfx_third=third&pfx_matchedRule={$rThree->getHash()}",
            'fourth' => "index.php?pfx_fourth=fourth&pfx_matchedRule={$rFour->getHash()}",
        ], $rewriteCollection->getRewriteRules());
        $this->assertSame(
            [$one, $two, $three],
            iterator_to_array($rewriteCollection->getRewrites())
        );
    }

    public function testLock()
    {
        $rewriteCollection = new RewriteCollection();

        $this->assertFalse($rewriteCollection->isLocked());

        $rewriteCollection->lock();

        $this->assertTrue($rewriteCollection->isLocked());
    }

    public function testShorthandMethods()
    {
        $collection = new RewriteCollection();
        $collection->any('someregex', 'index.php?var=value', 'somehandler');

        $rewrite = $collection->getRewrites()->current();
        $matchedRule = md5('someregex');

        $this->assertSame('somehandler', $rewrite->getHandler());
        $this->assertNull($rewrite->getIsActiveCallback());
        $this->assertSame(
            ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            $rewrite->getMethods()
        );
        $this->assertSame([
            'var' => 'var',
            'matchedRule' => 'matchedRule',
        ], $rewrite->getPrefixedToUnprefixedQueryVariablesMap());
        $this->assertSame(['var', 'matchedRule'], $rewrite->getQueryVariables());
        $this->assertSame([
            'someregex' => "index.php?var=value&matchedRule={$matchedRule}"
        ], $rewrite->getRewriteRules());
        $this->assertCount(1, $rewrite->getRules());

        $collection = new RewriteCollection();
        $collection->delete('someregex', 'index.php?var=value', 'somehandler');

        $this->assertSame(['DELETE'], $collection->getRewrites()->current()->getMethods());

        $collection = new RewriteCollection();
        $collection->get('someregex', 'index.php?var=value', 'somehandler');

        $this->assertSame(['GET', 'HEAD'], $collection->getRewrites()->current()->getMethods());

        $collection = new RewriteCollection();
        $collection->options('someregex', 'index.php?var=value', 'somehandler');

        $this->assertSame(['OPTIONS'], $collection->getRewrites()->current()->getMethods());

        $collection = new RewriteCollection();
        $collection->patch('someregex', 'index.php?var=value', 'somehandler');

        $this->assertSame(['PATCH'], $collection->getRewrites()->current()->getMethods());

        $collection = new RewriteCollection();
        $collection->post('someregex', 'index.php?var=value', 'somehandler');

        $this->assertSame(['POST'], $collection->getRewrites()->current()->getMethods());

        $collection = new RewriteCollection();
        $collection->put('someregex', 'index.php?var=value', 'somehandler');

        $this->assertSame(['PUT'], $collection->getRewrites()->current()->getMethods());
    }
}
