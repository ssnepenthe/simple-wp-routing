<?php

namespace ToyWpRouting\Tests;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\Rewrite;
use ToyWpRouting\RewriteCollection;
use ToyWpRouting\RewriteCollectionDumper;

class RewriteCollectionDumperTest extends TestCase
{
	public function testToFile()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite('GET', '/first/', ['first' => 'first'], 'somehandler', 'pfx_');
		$two = new Rewrite('POST', '/second/', ['second' => 'second'], 'anotherhandler');
		$two->setIsActiveCallback('isActive');

		$rewriteCollection->addMany([$one, $two]);

		$root = vfsStream::setup();
		$dumper = new RewriteCollectionDumper($rewriteCollection);

		$dumper->toFile($root->url());

		$this->assertTrue($root->hasChild('rewrite-cache.php'));
		$this->assertSame($dumper->toArray(), include $root->getChild('rewrite-cache.php')->url());
	}

	public function testToFileClosureCallbacks()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite('GET', '/first/', ['first' => 'first'], function() {}, 'pfx_');
		$two = new Rewrite('POST', '/second/', ['second' => 'second'], 'anotherhandler');
		$two->setIsActiveCallback(function() { return true; });

		$rewriteCollection->addMany([$one, $two]);

		$root = vfsStream::setup();
		(new RewriteCollectionDumper($rewriteCollection))->toFile($root->url());

		$dumped = include $root->getChild('rewrite-cache.php')->url();

		$this->assertStringStartsWith(
			'C:32:"Opis\Closure\SerializableClosure"',
			$dumped[0]['handler']
		);
		$this->assertStringStartsWith(
			'C:32:"Opis\Closure\SerializableClosure"',
			$dumped[1]['isActiveCallback']
		);
	}

	public function testToFileDirectoryDoesNotExist()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite('GET', '/first/', ['first' => 'first'], 'somehandler');

		$rewriteCollection->add($one);

		$root = vfsStream::setup();
		$dumper = new RewriteCollectionDumper($rewriteCollection);

		$dumper->toFile($root->url() . '/cache');

		$this->assertTrue($root->hasChild('cache'));
	}

	public function testToArray()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite('GET', '/first/', ['first' => 'first'], 'somehandler', 'pfx_');
		$two = new Rewrite('POST', '/second/', ['second' => 'second'], 'anotherhandler');
		$two->setIsActiveCallback('isActive');

		$rewriteCollection->addMany([$one, $two]);

		$this->assertSame([
			[
				'handler' => 'somehandler',
				'isActiveCallback' => null,
				'method' => 'GET',
				'prefixedToUnprefixedQueryVariablesMap' => ['pfx_first' => 'first'],
				'query' => 'index.php?pfx_first=first',
				'queryVariables' => ['pfx_first'],
				'regex' => '/first/',
			],
			[
				'handler' => 'anotherhandler',
				'isActiveCallback' => 'isActive',
				'method' => 'POST',
				'prefixedToUnprefixedQueryVariablesMap' => ['second' => 'second'],
				'query' => 'index.php?second=second',
				'queryVariables' => ['second'],
				'regex' => '/second/',
			],
		], (new RewriteCollectionDumper($rewriteCollection))->toArray());
	}
}
