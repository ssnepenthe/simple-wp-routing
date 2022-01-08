<?php

namespace ToyWpRouting\Tests;

use Closure;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionDumper;
use ToyWpRouting\RewriteCollectionLoader;
use ToyWpRouting\RouteCollection;
use ToyWpRouting\RouteConverter;

class RewriteCollectionLoaderTest extends TestCase
{
	public function testFromArray()
	{
		$loader = new RewriteCollectionLoader('irrelevant');
		$rewriteCollection = $loader->fromArray([
			[
				'methods' => ['GET'],
				'rules' => ['/regexone/' => 'index.php?pfx_one=one'],
				'handler' => 'handlerone',
				'prefixedToUnprefixedQueryVariablesMap' => ['pfx_one' => 'one'],
				'queryVariables' => ['pfx_one'],
				'isActiveCallback' => null,
			],
			[
				'methods' => ['POST'],
				'rules' => ['/regextwo/' => 'index.php?pfx_two=two'],
				'handler' => 'handlertwo',
				'prefixedToUnprefixedQueryVariablesMap' => ['pfx_two' => 'two'],
				'queryVariables' => ['pfx_two'],
				'isActiveCallback' => 'isactivetwo',
			],
		]);

		$this->assertInstanceOf(RewriteCollection::class, $rewriteCollection);
		$this->assertCount(2, $rewriteCollection->getRewrites());

		$this->assertNull($rewriteCollection->getRewrites()[0]->getIsActiveCallback());
		$this->assertSame(['GET'], $rewriteCollection->getRewrites()[0]->getMethods());
		$this->assertSame(
			['/regexone/' => 'index.php?pfx_one=one'],
			$rewriteCollection->getRewrites()[0]->getRules()
		);

		$this->assertSame(
			'isactivetwo',
			$rewriteCollection->getRewrites()[1]->getIsActiveCallback()
		);
		$this->assertSame(['POST'], $rewriteCollection->getRewrites()[1]->getMethods());
		$this->assertSame(
			['/regextwo/' => 'index.php?pfx_two=two'],
			$rewriteCollection->getRewrites()[1]->getRules()
		);
	}


	/** @dataProvider provideRequiredRewriteArrayKeys */
	public function testFromArrayWithIncorrectShape($toUnset)
	{
		$this->expectException(InvalidArgumentException::class);

		$rewriteArray = [
			'methods' => ['GET'],
			'rules' => ['/regexone/' => 'index.php?pfx_one=one'],
			'handler' => 'handlerone',
			'prefixedToUnprefixedQueryVariablesMap' => ['pfx_one' => 'one'],
			'queryVariables' => ['pfx_one'],
			'isActiveCallback' => null,
		];

		unset($rewriteArray[$toUnset]);

		(new RewriteCollectionLoader('irrelevant'))->fromArray([$rewriteArray]);
	}

	public function testFromCache()
	{
		$root = vfsStream::setup();

		{
			$rewriteCollection = new RewriteCollection();
			$rewriteCollection->add(
				new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'firsthandler')
			);
			$rewriteCollection->add(
				new Rewrite(
					['POST'],
					['/second/' => ['second' => 'second']],
					'secondhandler',
					'',
					'secondisactive'
				)
			);
			(new RewriteCollectionDumper($rewriteCollection))->toFile($root->url());
			unset($rewriteCollection);
		}

		$loader = new RewriteCollectionLoader($root->url());
		$rewriteCollection = $loader->fromCache();

		$this->assertInstanceOf(RewriteCollection::class, $rewriteCollection);
		$this->assertCount(2, $rewriteCollection->getRewrites());

		$this->assertNull($rewriteCollection->getRewrites()[0]->getIsActiveCallback());
		$this->assertSame(['GET'], $rewriteCollection->getRewrites()[0]->getMethods());
		$this->assertSame(
			['/first/' => 'index.php?first=first'],
			$rewriteCollection->getRewrites()[0]->getRules()
		);

		$this->assertSame(
			'secondisactive',
			$rewriteCollection->getRewrites()[1]->getIsActiveCallback()
		);
		$this->assertSame(['POST'], $rewriteCollection->getRewrites()[1]->getMethods());
		$this->assertSame(
			['/second/' => 'index.php?second=second'],
			$rewriteCollection->getRewrites()[1]->getRules()
		);
	}

	public function testFromCacheWithSerializedClosures()
	{
		$root = vfsStream::setup();

		{
			$rewriteCollection = new RewriteCollection();
			$rewriteCollection->add(
				new Rewrite(
					['GET'],
					['/regex/' => ['rewrite' => 'rewrite']],
					function() {},
					'',
					function() { return true; }
				)
			);
			(new RewriteCollectionDumper($rewriteCollection))->toFile($root->url());
			unset($rewriteCollection);
		}

		$loader = new RewriteCollectionLoader($root->url());
		$rewriteCollection = $loader->fromCache();
		$rewrite = $rewriteCollection->getRewrites()[0];

		$this->assertInstanceOf(Closure::class, $rewrite->getHandler());
		$this->assertInstanceOf(Closure::class, $rewrite->getIsActiveCallback());
	}

	public function testFromRouteCollection()
	{
		$loader = new RewriteCollectionLoader('irrelevant');

		$routeCollection = new RouteCollection();
		$routeCollection->add(['GET'], 'one', 'handlerone');
		$routeCollection->post('two', 'handlertwo');

		$rewriteCollection = $loader->fromRouteCollection($routeCollection);

		$this->assertInstanceOf(RewriteCollection::class, $rewriteCollection);
		$this->assertCount(2, $rewriteCollection->getRewrites());

		$this->assertSame(['GET'], $rewriteCollection->getRewrites()[0]->getMethods());
		$this->assertSame(
			['^one$' => 'index.php?matchedRoute=' . md5('^one$')],
			$rewriteCollection->getRewrites()[0]->getRules()
		);

		$this->assertSame(['POST'], $rewriteCollection->getRewrites()[1]->getMethods());
		$this->assertSame(
			['^two$' => 'index.php?matchedRoute=' . md5('^two$')],
			$rewriteCollection->getRewrites()[1]->getRules()
		);
	}

	public function testHasCachedRewrites()
	{
		$root = vfsStream::setup();
		$loader = new RewriteCollectionLoader($root->url());

		$this->assertFalse($loader->hasCachedRewrites());

		touch($root->url() . '/rewrite-cache.php');

		$this->assertTrue($loader->hasCachedRewrites());
	}

	public function testRouteConverter()
	{
		$loader = new RewriteCollectionLoader('irrelevant');
		$converter = new RouteConverter();

		$defaultConverter = $loader->getRouteConverter();

		$this->assertInstanceOf(RouteConverter::class, $defaultConverter);
		$this->assertNotSame($converter, $defaultConverter);

		$loader->setRouteConverter($converter);

		$this->assertSame($converter, $loader->getRouteConverter());
	}

	public function provideRequiredRewriteArrayKeys()
	{
		yield ['methods'];
		yield ['rules'];
		yield ['handler'];
		yield ['prefixedToUnprefixedQueryVariablesMap'];
		yield ['queryVariables'];
		yield ['isActiveCallback'];
	}
}
