<?php

declare(strict_types=1);

namespace SimpleWpRouting\Tests\Unit\Support;

use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleWpRouting\Support\Rewrite;
use SimpleWpRouting\Support\RewriteCollection;

class RewriteCollectionTest extends TestCase
{
    public function testAdd()
    {
        $rewriteCollection = new RewriteCollection();
        $rewrite = new Rewrite(['GET'], 'someregex', 'index.php?var=value', ['var' => 'var'], 'somehandler');

        $rewriteCollection->add($rewrite);

        $this->assertSame([$rewrite], $rewriteCollection->getRewrites());
    }

    public function testAddDuplicateRewrites()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('already registered');

        $rewriteCollection = new RewriteCollection();
        $rewrite = new Rewrite(['GET'], 'someregex', 'index.php?var=value', ['var' => 'var'], 'somehandler');

        $rewriteCollection->add($rewrite);
        $rewriteCollection->add($rewrite);
    }

    public function testAddWhenLocked()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot add rewrites when rewrite collection is locked');

        $rewriteCollection = new RewriteCollection();
        $rewriteCollection->lock();

        $rewriteCollection->add(new Rewrite(['GET'], 'someregex', 'index.php?var=value', ['var' => 'var'], 'somehandler'));
    }

    public function testEmpty()
    {
        $rewriteCollection = new RewriteCollection();

        $this->assertTrue($rewriteCollection->empty());

        $rewriteCollection->add(new Rewrite(['GET'], 'regex', 'query', [], 'handler'));

        $this->assertFalse($rewriteCollection->empty());
    }

    public function testFindByRegex()
    {
        $regex = 'someregex';

        $rewriteCollection = new RewriteCollection();

        // Multiple methods, single regex.
        $rewriteOne = new Rewrite(['GET', 'POST'], $regex, 'index.php?var=value', ['var' => 'var'], 'somehandler');

        // Same regex, additional method.
        $rewriteTwo = new Rewrite(['PUT'], $regex, 'index.php?var=value', ['var' => 'var'], 'somehandler');

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
        $one = new Rewrite(['GET', 'HEAD'], 'first', 'index.php?first=first', ['first' => 'first'], 'somehandler');
        $two = new Rewrite(['POST'], 'second', 'index.php?second=second', ['second' => 'second'], 'somehandler');
        $two->setIsActiveCallback(fn () => false);
        $three = new Rewrite(['POST'], 'third', 'index.php?third=third&fourth=fourth', ['third' => 'third', 'fourth' => 'fourth'], 'somehandler');

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
        $this->assertSame([$one, $two, $three], $rewriteCollection->getRewrites());
    }

    public function testLock()
    {
        $rewriteCollection = new RewriteCollection();

        $this->assertFalse($rewriteCollection->isLocked());

        $rewriteCollection->lock();

        $this->assertTrue($rewriteCollection->isLocked());
    }
}
