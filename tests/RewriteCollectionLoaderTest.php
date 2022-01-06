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
				'handler' => 'handlerone',
				'isActiveCallback' => null,
				'method' => 'GET',
				'prefixedToUnprefixedQueryVariablesMap' => ['pfx_one' => 'one'],
				'query' => 'index.php?pfx_one=one',
				'queryVariables' => ['pfx_one'],
				'regex' => '/regexone/',
			],
			[
				'handler' => 'handlertwo',
				'isActiveCallback' => 'isactivetwo',
				'method' => 'POST',
				'prefixedToUnprefixedQueryVariablesMap' => ['pfx_two' => 'two'],
				'query' => 'index.php?pfx_two=two',
				'queryVariables' => ['pfx_two'],
				'regex' => '/regextwo/',
			],
		]);

		$this->assertInstanceOf(RewriteCollection::class, $rewriteCollection);
		$this->assertCount(2, $rewriteCollection->getRewrites());
		$this->assertNull($rewriteCollection->getRewrites()[0]->getIsActiveCallback());
		$this->assertSame('GET', $rewriteCollection->getRewrites()[0]->getMethod());
		$this->assertSame('/regexone/', $rewriteCollection->getRewrites()[0]->getRegex());
		$this->assertSame(
			'isactivetwo',
			$rewriteCollection->getRewrites()[1]->getIsActiveCallback()
		);
		$this->assertSame('POST', $rewriteCollection->getRewrites()[1]->getMethod());
		$this->assertSame('/regextwo/', $rewriteCollection->getRewrites()[1]->getRegex());
	}


	/** @dataProvider provideRequiredRewriteArrayKeys */
	public function testFromArrayWithIncorrectShape($toUnset)
	{
		$this->expectException(InvalidArgumentException::class);

		$rewriteArray = [
			'handler' => 'handlerone',
			'isActiveCallback' => null,
			'method' => 'GET',
			'prefixedToUnprefixedQueryVariablesMap' => ['pfx_one' => 'one'],
			'query' => 'index.php?pfx_one=one',
			'queryVariables' => ['pfx_one'],
			'regex' => '/regexone/',
		];

		unset($rewriteArray[$toUnset]);

		(new RewriteCollectionLoader('irrelevant'))->fromArray([$rewriteArray]);
	}

	public function testFromCache()
	{
		$root = vfsStream::setup();

		{
			$rewriteCollection = new RewriteCollection();
			$rewrite = new Rewrite('POST', '/second/', ['second' => 'second'], 'secondhandler');
			$rewrite->setIsActiveCallback('secondisactive');
			$rewriteCollection->addMany([
				new Rewrite('GET', '/first/', ['first' => 'first'], 'firsthandler'),
				$rewrite,
			]);
			(new RewriteCollectionDumper($rewriteCollection))->toFile($root->url());
			unset($rewriteCollection, $rewrite);
		}

		$loader = new RewriteCollectionLoader($root->url());
		$rewriteCollection = $loader->fromCache();

		$this->assertInstanceOf(RewriteCollection::class, $rewriteCollection);
		$this->assertCount(2, $rewriteCollection->getRewrites());
		$this->assertNull($rewriteCollection->getRewrites()[0]->getIsActiveCallback());
		$this->assertSame('GET', $rewriteCollection->getRewrites()[0]->getMethod());
		$this->assertSame('/first/', $rewriteCollection->getRewrites()[0]->getRegex());
		$this->assertSame(
			'secondisactive',
			$rewriteCollection->getRewrites()[1]->getIsActiveCallback()
		);
		$this->assertSame('POST', $rewriteCollection->getRewrites()[1]->getMethod());
		$this->assertSame('/second/', $rewriteCollection->getRewrites()[1]->getRegex());
	}

	public function testFromCacheWithSerializedClosures()
	{
		$root = vfsStream::setup();

		{
			$rewriteCollection = new RewriteCollection();
			$rewrite = new Rewrite('GET', '/regex/', ['rewrite' => 'rewrite'], function() {});
			$rewrite->setIsActiveCallback(function() { return true; });
			$rewriteCollection->add($rewrite);
			(new RewriteCollectionDumper($rewriteCollection))->toFile($root->url());
			unset($rewriteCollection, $rewrite);
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
		$this->assertSame('GET', $rewriteCollection->getRewrites()[0]->getMethod());
		$this->assertSame('^one$', $rewriteCollection->getRewrites()[0]->getRegex());
		$this->assertSame('POST', $rewriteCollection->getRewrites()[1]->getMethod());
		$this->assertSame('^two$', $rewriteCollection->getRewrites()[1]->getRegex());
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
		yield ['handler'];
		yield ['isActiveCallback'];
		yield ['method'];
		yield ['prefixedToUnprefixedQueryVariablesMap'];
		yield ['query'];
		yield ['queryVariables'];
		yield ['regex'];
	}
}
