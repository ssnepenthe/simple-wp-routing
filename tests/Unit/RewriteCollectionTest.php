<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Exception\MethodNotAllowedHttpException;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
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

        $this->assertSame(['var', 'matchedRule'], $rewriteCollection->getActiveQueryVariables());
    }

    public function testAddWhenLocked()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot add rewrites when rewrite collection is locked');

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
        $two->setIsActiveCallback(fn () => false);
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

        $this->assertSame(
            ['first', 'matchedRule', 'third', 'fourth'],
            $rewriteCollection->getActiveQueryVariables()
        );
        $this->assertSame([
            'first' => "index.php?first=first&matchedRule={$rOne->getHash()}",
            'second' => "index.php?second=second&matchedRule={$rTwo->getHash()}",
            'third' => "index.php?third=third&matchedRule={$rThree->getHash()}",
            'fourth' => "index.php?fourth=fourth&matchedRule={$rFour->getHash()}",
        ], $rewriteCollection->getRewriteRules());
        $this->assertSame([
            'first' => "index.php?first=first&matchedRule={$rOne->getHash()}",
            'third' => "index.php?third=third&matchedRule={$rThree->getHash()}",
            'fourth' => "index.php?fourth=fourth&matchedRule={$rFour->getHash()}",
        ], $rewriteCollection->getActiveRewriteRules());
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
        $two->setIsActiveCallback(fn () => false);
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
            ['pfx_first', 'pfx_matchedRule', 'pfx_third', 'pfx_fourth'],
            $rewriteCollection->getActiveQueryVariables()
        );
        $this->assertSame([
            'first' => "index.php?pfx_first=first&pfx_matchedRule={$rOne->getHash()}",
            'second' => "index.php?pfx_second=second&pfx_matchedRule={$rTwo->getHash()}",
            'third' => "index.php?pfx_third=third&pfx_matchedRule={$rThree->getHash()}",
            'fourth' => "index.php?pfx_fourth=fourth&pfx_matchedRule={$rFour->getHash()}",
        ], $rewriteCollection->getRewriteRules());
        $this->assertSame([
            'first' => "index.php?pfx_first=first&pfx_matchedRule={$rOne->getHash()}",
            'third' => "index.php?pfx_third=third&pfx_matchedRule={$rThree->getHash()}",
            'fourth' => "index.php?pfx_fourth=fourth&pfx_matchedRule={$rFour->getHash()}",
        ], $rewriteCollection->getActiveRewriteRules());
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
