<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;

class RewriteCollectionTest extends TestCase
{
    public function testAdd()
    {
        $rewriteCollection = new RewriteCollection();
        $rewrite = new Rewrite(['GET'], 'someregex', 'index.php?var=value', 'somehandler');

        $rewriteCollection->add($rewrite);

        $this->assertSame([$rewrite], iterator_to_array($rewriteCollection->getRewrites()));
    }

    public function testAddDuplicateRewrites()
    {
        $rewriteCollection = new RewriteCollection();
        $rewrite = new Rewrite(['GET'], 'someregex', 'index.php?var=value', 'somehandler');

        $rewriteCollection->add($rewrite);
        $rewriteCollection->add($rewrite);

        // Rewrites are only added once.
        $this->assertCount(1, $rewriteCollection->getRewrites());

        // Rewrite rules and query variables are unique.
        $this->assertSame(['someregex' => 'index.php?var=value'], $rewriteCollection->getRewriteRules());

        $this->assertSame(['var'], $rewriteCollection->getQueryVariables());
    }

    public function testAddWhenLocked()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot add rewrites when rewrite collection is locked');

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->lock();

        $rewriteCollection->add(new Rewrite(['GET'], 'someregex', 'index.php?var=value', 'somehandler'));
    }

    public function testAddWithPrefix()
    {
        // Prefix should not be applied to rewrites via add method, only via create method.
        $collection = new RewriteCollection('pfx_');
        $collection->add(new Rewrite(['GET'], [new RewriteRule('someregex', 'index.php?var=value')], 'somehandler'));

        $this->assertSame([
            'someregex' => 'index.php?var=value'
        ], $collection->getRewriteRules());
    }

    public function testFindActiveRewriteByHashAndMethod()
    {
        $rewriteCollection = new RewriteCollection();

        // Multiple methods, single rule.
        $ruleOne = new RewriteRule('someregex', 'index.php?var=value');
        $rewriteOne = new Rewrite(['GET', 'POST'], [$ruleOne], 'somehandler');

        // Same rule, additional method.
        $rewriteTwo = new Rewrite(['PUT'], [$ruleOne], 'somehandler');

        // Single method, multiple rules with one same as previous.
        $ruleThree = new RewriteRule('anotherregex', 'index.php?anothervar=anothervalue');
        $rewriteThree = new Rewrite(['DELETE'], [$ruleOne, $ruleThree], 'somehandler');

        // Same rule, additional method.
        $rewriteFour = new Rewrite(['GET'], [$ruleThree], 'somehandler');

        $rewriteCollection->add($rewriteOne);
        $rewriteCollection->add($rewriteTwo);
        $rewriteCollection->add($rewriteThree);
        $rewriteCollection->add($rewriteFour);

        $this->assertSame(
            $rewriteOne,
            $rewriteCollection->findActiveRewriteByHashAndMethod($ruleOne->getHash(), 'GET')
        );
        $this->assertSame(
            $rewriteOne,
            $rewriteCollection->findActiveRewriteByHashAndMethod($ruleOne->getHash(), 'POST')
        );
        $this->assertSame(
            $rewriteTwo,
            $rewriteCollection->findActiveRewriteByHashAndMethod($ruleOne->getHash(), 'PUT')
        );
        $this->assertSame(
            $rewriteThree,
            $rewriteCollection->findActiveRewriteByHashAndMethod($ruleOne->getHash(), 'DELETE')
        );
        $this->assertSame(
            $rewriteThree,
            $rewriteCollection->findActiveRewriteByHashAndMethod($ruleThree->getHash(), 'DELETE')
        );
        $this->assertSame(
            $rewriteFour,
            $rewriteCollection->findActiveRewriteByHashAndMethod($ruleThree->getHash(), 'GET')
        );
    }

    public function testFindActiveRewriteByHashAndMethodWhenMethodIsNotAllowed()
    {
        $exception = null;

        $rewriteCollection = new RewriteCollection();

        $rule = new RewriteRule('someregex', 'index.php?var=value');
        $rewrite = new Rewrite(['GET', 'POST'], [$rule], 'somehandler');

        $rewriteCollection->add($rewrite);

        try {
            $rewriteCollection->findActiveRewriteByHashAndMethod($rule->getHash(), 'PUT');
        } catch (MethodNotAllowedHttpException $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(MethodNotAllowedHttpException::class, $exception);
        $this->assertSame(['Allow' => 'GET, POST'], $exception->getHeaders());
    }

    public function testFindActiveRewriteByHashAndMethodWhenRewriteDoesntExist()
    {
        $rewriteCollection = new RewriteCollection();

        $rule = new RewriteRule('someregex', 'index.php?var=value');
        $rewrite = new Rewrite(['GET'], [$rule], 'somehandler');

        $rewriteCollection->add($rewrite);

        $this->assertNull($rewriteCollection->findActiveRewriteByHashAndMethod('badhash', 'GET'));
    }

    public function testGetters()
    {
        $one = new Rewrite(['GET', 'HEAD'], 'first', 'index.php?first=first', 'somehandler');
        $two = new Rewrite(['POST'], 'second', 'index.php?second=second', 'somehandler');
        $two->setIsActiveCallback(fn () => false);
        $three = new Rewrite(['POST'], 'third', 'index.php?third=third&fourth=fourth', 'somehandler');

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->add($one);
        $rewriteCollection->add($two);
        $rewriteCollection->add($three);

        $this->assertSame(
            ['first', 'second', 'third', 'fourth'],
            $rewriteCollection->getQueryVariables()
        );
        $this->assertSame([
            'first' => 'index.php?first=first',
            'second' => 'index.php?second=second',
            'third' => 'index.php?third=third&fourth=fourth',
        ], $rewriteCollection->getRewriteRules());
        $this->assertSame(
            [$one, $two, $three],
            iterator_to_array($rewriteCollection->getRewrites())
        );
    }

    public function testGettersWithPrefixedRewrites()
    {
        $prefix = 'pfx_';

        $one = new Rewrite(['GET', 'HEAD'], 'first', 'index.php?first=first', 'somehandler', $prefix);
        $two = new Rewrite(['POST'], 'second', 'index.php?second=second', 'somehandler', $prefix);
        $two->setIsActiveCallback(fn () => false);
        $three = new Rewrite(['POST'], 'third', 'index.php?third=third&fourth=fourth', 'somehandler', $prefix);

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->add($one);
        $rewriteCollection->add($two);
        $rewriteCollection->add($three);

        $this->assertSame(
            ['pfx_first', 'pfx_second', 'pfx_third', 'pfx_fourth'],
            $rewriteCollection->getQueryVariables()
        );
        $this->assertSame([
            'first' => 'index.php?pfx_first=first',
            'second' => 'index.php?pfx_second=second',
            'third' => 'index.php?pfx_third=third&pfx_fourth=fourth',
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
}
