<?php

namespace ToyWpRouting\Tests;

use DI\Container;
use Invoker\Invoker;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteInterface;

use function DI\value;

class RewriteCollectionTest extends TestCase
{
	public function testAdd()
	{
		$rewriteCollection = new RewriteCollection();
		$rewrite = new Rewrite(['GET'], ['/someregex/' => ['var' => 'value']], 'somehandler');

		$rewriteCollection->add($rewrite);

		$this->assertSame([$rewrite], $rewriteCollection->getRewrites());
	}

	public function testAddWhenLocked()
	{
		$this->expectException(RuntimeException::class);

		$rewriteCollection = new RewriteCollection();
		$rewriteCollection->lock();

		$rewriteCollection->add(
			new Rewrite(['GET'], ['/someregex/' => ['var' => 'value']], 'somehandler')
		);
	}

	public function testAddDuplicateRewrites()
	{
		$rewriteCollection = new RewriteCollection();
		$rewrite = new Rewrite(['GET'], ['/someregex/' => ['var' => 'value']], 'somehandler');

		$rewriteCollection->add($rewrite);
		$rewriteCollection->add($rewrite);

		// All rewrites are added.
		// @todo Should this still be the case?
		$this->assertCount(2, $rewriteCollection->getRewrites());

		// But rewrite rules and query variables are unique.
		$this->assertCount(1, $rewriteCollection->getRewriteRules());
		$this->assertCount(1, $rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap());
		$this->assertCount(1, $rewriteCollection->getQueryVariables());
	}

	public function testFilter()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'somehandler');
		$two = new Rewrite(['POST'], ['/second/' => ['second' => 'second']], 'somehandler');
		$three = new Rewrite(['GET'], ['/third/' => ['third' => 'third']], 'somehandler');

		$rewriteCollection->add($one);
		$rewriteCollection->add($two);
		$rewriteCollection->add($three);

		$filtered = $rewriteCollection->filter(function(RewriteInterface $rewrite) {
			return ['GET'] === $rewrite->getMethods();
		});

		$this->assertInstanceOf(RewriteCollection::class, $filtered);
		$this->assertNotSame($rewriteCollection, $filtered);

		$this->assertSame([$one, $three], $filtered->getRewrites());
	}

	public function testFilterWhenLocked()
	{
		$rewriteCollection = new RewriteCollection();

		$rewriteCollection->add(
			new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'somehandler')
		);

		$this->assertFalse($rewriteCollection->filter(function() { return true; })->isLocked());

		$rewriteCollection->lock();

		$this->assertTrue($rewriteCollection->filter(function() { return true; })->isLocked());
	}

	public function testLock()
	{
		$rewriteCollection = new RewriteCollection();

		$this->assertFalse($rewriteCollection->isLocked());

		$rewriteCollection->lock();

		$this->assertTrue($rewriteCollection->isLocked());
	}

	public function testGetRewritesByRegexHash()
	{
		$rewriteCollection = new RewriteCollection();

		$hash = md5('/someregex/');
		// Multiple methods, single rule.
		$one = new Rewrite(['GET', 'POST'], ['/someregex/' => ['var' => 'value']], 'somehandler');
		// Same regex, different method.
		$two = new Rewrite(['PUT'], ['/someregex/' => ['var' => 'value']], 'somehandler');
		// Single method, multiple rules - one regex is the same as previous.
		$three = new Rewrite(
			['DELETE'],
			[
				'/someregex/' => ['var' => 'value'],
				'/anotherregex/' => ['anothervar' => 'anothervalue'],
			],
			'somehandler'
		);
		// Different rule, same method as previous.
		$four = new Rewrite(['GET'], ['/anotherregex/' => ['variable' => 'value']], 'somehandler');

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

		$hash = md5('/anotherregex/');
		$one = new Rewrite(['GET'], ['/someregex/' => ['var' => 'value']], 'somehandler');

		$rewriteCollection->add($one);

		$this->assertSame([], $rewriteCollection->getRewritesByRegexHash($hash));
	}

	public function testGetters()
	{
		$one = new Rewrite(['GET', 'HEAD'], ['/first/' => ['first' => 'first']], 'somehandler');
		$two = new Rewrite(['POST'], ['/second/' => ['second' => 'second']], 'somehandler');
		$three = new Rewrite(
			['POST'],
			['/third/' => ['third' => 'third'], '/fourth/' => ['fourth' => 'fourth']],
			'somehandler'
		);

		$rewriteCollection = new RewriteCollection();
		$rewriteCollection->add($one);
		$rewriteCollection->add($two);
		$rewriteCollection->add($three);

		$this->assertSame(
			['first' => 'first', 'second' => 'second', 'third' => 'third', 'fourth' => 'fourth'],
			$rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap()
		);
		$this->assertSame(
			['first', 'second', 'third', 'fourth'],
			$rewriteCollection->getQueryVariables()
		);
		$this->assertSame([$one, $two, $three], $rewriteCollection->getRewrites());
		$this->assertSame([
			'/first/' => 'index.php?first=first',
			'/second/' => 'index.php?second=second',
			'/third/' => 'index.php?third=third',
			'/fourth/' => 'index.php?fourth=fourth',
		], $rewriteCollection->getRewriteRules());
	}

	public function testGettersWithPrefixedRewrites()
	{
		$one = new Rewrite(
			['GET', 'HEAD'],
			['/first/' => ['first' => 'first']],
			'somehandler',
			'pfx_'
		);
		$two = new Rewrite(['POST'], ['/second/' => ['second' => 'second']], 'somehandler', 'pfx_');
		$three = new Rewrite(
			['POST'],
			['/third/' => ['third' => 'third'], '/fourth/' => ['fourth' => 'fourth']],
			'somehandler',
			'pfx_'
		);

		$rewriteCollection = new RewriteCollection();
		$rewriteCollection->add($one);
		$rewriteCollection->add($two);
		$rewriteCollection->add($three);

		$this->assertSame(
			[
				'pfx_first' => 'first',
				'pfx_second' => 'second',
				'pfx_third' => 'third',
				'pfx_fourth' => 'fourth',
			],
			$rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap()
		);
		$this->assertSame(
			['pfx_first', 'pfx_second', 'pfx_third', 'pfx_fourth'],
			$rewriteCollection->getQueryVariables()
		);
		$this->assertSame([$one, $two, $three], $rewriteCollection->getRewrites());
		$this->assertSame([
			'/first/' => 'index.php?pfx_first=first',
			'/second/' => 'index.php?pfx_second=second',
			'/third/' => 'index.php?pfx_third=third',
			'/fourth/' => 'index.php?pfx_fourth=fourth',
		], $rewriteCollection->getRewriteRules());
	}
}
