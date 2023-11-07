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

    public function testFindByRegex()
    {
        $regex = 'someregex';

        $rewriteCollection = new RewriteCollection();

        // Multiple methods, single regex.
        $rewriteOne = new Rewrite(['GET', 'POST'], $regex, 'index.php?var=value', 'somehandler');

        // Same regex, additional method.
        $rewriteTwo = new Rewrite(['PUT'], $regex, 'index.php?var=value', 'somehandler');

        $rewriteCollection->add($rewriteOne);
        $rewriteCollection->add($rewriteTwo);

        $this->assertSame([], $rewriteCollection->findByRegex('notregistered'));
        $this->assertSame(
            ['GET' => $rewriteOne, 'POST' => $rewriteOne, 'PUT' => $rewriteTwo],
            $rewriteCollection->findByRegex($regex)
        );
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
