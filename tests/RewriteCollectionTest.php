<?php

namespace ToyWpRouting\Tests;

use DI\Container;
use Invoker\Invoker;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;

use function DI\value;

class RewriteCollectionTest extends TestCase
{
	public function testAdd()
	{
		$rewriteCollection = new RewriteCollection();
		$rewrite = new Rewrite('GET', '/someregex/', ['var' => 'value'], 'somehandler');

		$rewriteCollection->add($rewrite);

		$this->assertSame([$rewrite], $rewriteCollection->getRewrites());
	}

	public function testAddWhenLocked()
	{
		$this->expectException(RuntimeException::class);

		$rewriteCollection = new RewriteCollection();
		$rewriteCollection->lock();

		$rewriteCollection->add(
			new Rewrite('GET', '/someregex/', ['var' => 'value'], 'somehandler')
		);
	}

	public function testAddDuplicateRewrites()
	{
		$rewriteCollection = new RewriteCollection();
		$rewrite = new Rewrite('GET', '/someregex/', ['var' => 'value'], 'somehandler');

		$rewriteCollection->add($rewrite);
		$rewriteCollection->add($rewrite);

		// All rewrites are added.
		$this->assertCount(2, $rewriteCollection->getRewrites());

		// But rewrite rules and query variables are unique.
		$this->assertCount(1, $rewriteCollection->getRewriteRules());
		$this->assertCount(1, $rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap());
		$this->assertCount(1, $rewriteCollection->getQueryVariables());
	}

	public function testAddMany()
	{
		$rewriteCollection = new RewriteCollection();
		$one = new Rewrite('GET', '/first/', ['first' => 'first'], 'somehandler');
		$two = new Rewrite('GET', '/second/', ['second' => 'second'], 'somehandler');

		$rewriteCollection->addMany([$one, $two]);

		$this->assertSame([$one, $two], $rewriteCollection->getRewrites());
	}

	public function testFilterActiveRewrites()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite('GET', '/first/', ['first' => 'first'], 'somehandler');
		$one->setIsActiveCallback(function() { return true; });

		$two = new Rewrite('GET', '/second/', ['second' => 'second'], 'somehandler');
		$two->setIsActiveCallback(function() { return false; });

		$three = new Rewrite('GET', '/third/', ['third' => 'third'], 'somehandler');
		$three->setIsActiveCallback(function() { return true; });

		$four = new Rewrite('GET', '/fourth/', ['fourth' => 'fourth'], 'somehandler');

		$rewriteCollection->addMany([$one, $two, $three, $four]);
		$active = $rewriteCollection->filterActiveRewrites();

		// Returns a new collection instance.
		$this->assertInstanceOf(RewriteCollection::class, $active);
		$this->assertNotSame($rewriteCollection, $active);

		// Populated with only rewrites that are considered active.
		$this->assertSame([$one, $three, $four], $active->getRewrites());
	}

	public function testFilterActiveRewritesWhenLocked()
	{
		$rewriteCollection = new RewriteCollection();
		$one = new Rewrite('GET', '/first/', ['first' => 'first'], 'somehandler');

		$rewriteCollection->add($one);

		$this->assertFalse($rewriteCollection->filterActiveRewrites()->isLocked());

		$rewriteCollection->lock();

		$this->assertTrue($rewriteCollection->filterActiveRewrites()->isLocked());
	}

	public function testFilterActiveRewritesWithInvoker()
	{
		$runCount = 0;

		$container = new Container();
		$container->set('truthy', value(true));
		$container->set('falsy', value(false));
		$container->set('callback', value(function() use (&$runCount) {
			$runCount++;
			return true;
		}));

		$invoker = new Invoker(new ParameterNameContainerResolver($container), $container);

		$rewriteCollection = new RewriteCollection();

		// Provides container values as params to active callback
		$one = new Rewrite('GET', '/first/', ['first' => 'first'], 'somehandler');
		$one->setIsActiveCallback(function($truthy) use (&$runCount) {
			$runCount++;
			return $truthy;
		});

		$two = new Rewrite('GET', '/second/', ['second' => 'second'], 'somehandler');
		$two->setIsActiveCallback(function($falsy) use (&$runCount) {
			$runCount++;
			return $falsy;
		});

		// Can resolve active callback from container.
		$three = new Rewrite('GET', '/third/', ['third' => 'third'], 'somehandler');
		$three->setIsActiveCallback('callback');

		$rewriteCollection->addMany([$one, $two, $three]);

		$this->assertSame(
			[$one, $three],
			$rewriteCollection->filterActiveRewrites($invoker)->getRewrites()
		);
		$this->assertSame(3, $runCount);
	}

	public function testGetRewritesByRegexHash()
	{
		$rewriteCollection = new RewriteCollection();

		$hash = md5('/someregex/');
		$one = new Rewrite('GET', '/someregex/', ['var' => 'value'], 'somehandler');
		// Same regex, different method.
		$two = new Rewrite('POST', '/someregex/', ['var' => 'value'], 'somehandler');
		// Different regex, same method.
		$three = new Rewrite('GET', '/anotherregex/', ['variable' => 'value'], 'somehandler');

		$rewriteCollection->addMany([$one, $two, $three]);

		$this->assertSame(
			['GET' => $one, 'POST' => $two],
			$rewriteCollection->getRewritesByRegexHash($hash)
		);
	}

	public function testGetRewritesByRegexHashNotFound()
	{
		$rewriteCollection = new RewriteCollection();

		$hash = md5('/anotherregex/');
		$one = new Rewrite('GET', '/someregex/', ['var' => 'value'], 'somehandler');

		$rewriteCollection->add($one);

		$this->assertSame([], $rewriteCollection->getRewritesByRegexHash($hash));
	}

	public function testLock()
	{
		$rewriteCollection = new RewriteCollection();

		$this->assertFalse($rewriteCollection->isLocked());

		$rewriteCollection->lock();

		$this->assertTrue($rewriteCollection->isLocked());
	}

	public function testMerge()
	{
		$rewriteCollectionOne = new RewriteCollection();
		$rewriteCollectionTwo = new RewriteCollection();

		$one = new Rewrite('GET', '/first/',['first' => 'first'], 'somehandler');
		$two = new Rewrite('POST', '/second/',['second' => 'second'], 'somehandler');
		$three = new Rewrite('POST', '/third/',['third' => 'third'], 'somehandler');

		$rewriteCollectionOne->addMany([$one, $two]);
		$rewriteCollectionTwo->add($three);

		$rewriteCollectionOne->merge($rewriteCollectionTwo);

		$this->assertSame([$one, $two, $three], $rewriteCollectionOne->getRewrites());
	}

	public function testGetters()
	{
		$one = new Rewrite('GET', '/first/',['first' => 'first'], 'somehandler');
		$two = new Rewrite('POST', '/second/',['second' => 'second'], 'somehandler');
		$three = new Rewrite('POST', '/third/',['third' => 'third'], 'somehandler');

		$rewriteCollection = new RewriteCollection();
		$rewriteCollection->addMany([$one, $two, $three]);

		$this->assertSame(
			['first' => 'first', 'second' => 'second', 'third' => 'third'],
			$rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap()
		);
		$this->assertSame(['first', 'second', 'third'], $rewriteCollection->getQueryVariables());
		$this->assertSame([$one, $two, $three], $rewriteCollection->getRewrites());
		$this->assertSame([
			'/first/' => 'index.php?first=first',
			'/second/' => 'index.php?second=second',
			'/third/' => 'index.php?third=third',
		], $rewriteCollection->getRewriteRules());
	}

	public function testGettersWithPrefixedRewrites()
	{
		$one = new Rewrite('GET', '/first/',['first' => 'first'], 'somehandler', 'pfx_');
		$two = new Rewrite('POST', '/second/',['second' => 'second'], 'somehandler', 'pfx_');
		$three = new Rewrite('POST', '/third/',['third' => 'third'], 'somehandler', 'pfx_');

		$rewriteCollection = new RewriteCollection();
		$rewriteCollection->addMany([$one, $two, $three]);

		$this->assertSame(
			['pfx_first' => 'first', 'pfx_second' => 'second', 'pfx_third' => 'third'],
			$rewriteCollection->getPrefixedToUnprefixedQueryVariablesMap()
		);
		$this->assertSame(
			['pfx_first', 'pfx_second', 'pfx_third'],
			$rewriteCollection->getQueryVariables()
		);
		$this->assertSame([$one, $two, $three], $rewriteCollection->getRewrites());
		$this->assertSame([
			'/first/' => 'index.php?pfx_first=first',
			'/second/' => 'index.php?pfx_second=second',
			'/third/' => 'index.php?pfx_third=third',
		], $rewriteCollection->getRewriteRules());
	}
}
