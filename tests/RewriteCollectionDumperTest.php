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

		$one = new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'somehandler', 'pfx_');
		$two = new Rewrite(
			['POST'],
			['/second/' => ['second' => 'second']],
			'anotherhandler',
			'',
			'isActive'
		);

		$rewriteCollection->add($one);
		$rewriteCollection->add($two);

		$root = vfsStream::setup();
		$dumper = new RewriteCollectionDumper($rewriteCollection);

		$dumper->toFile($root->url());

		$this->assertTrue($root->hasChild('rewrite-cache.php'));
		$this->assertSame($dumper->toArray(), include $root->getChild('rewrite-cache.php')->url());
	}

	public function testToFileClosureCallbacks()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite(['GET'], ['/first/' => ['first' => 'first']], function() {}, 'pfx_');
		$two = new Rewrite(
			['POST'],
			['/second/' => ['second' => 'second']],
			'anotherhandler',
			'',
			function() { return true; }
		);

		$rewriteCollection->add($one);
		$rewriteCollection->add($two);

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

		$one = new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'somehandler');

		$rewriteCollection->add($one);

		$root = vfsStream::setup();
		$dumper = new RewriteCollectionDumper($rewriteCollection);

		$this->assertFalse($root->hasChild('cache'));

		$dumper->toFile($root->url() . '/cache');

		$this->assertTrue($root->hasChild('cache'));
	}

	public function testToArray()
	{
		$rewriteCollection = new RewriteCollection();

		$one = new Rewrite(['GET'], ['/first/' => ['first' => 'first']], 'somehandler', 'pfx_');
		$two = new Rewrite(
			['POST'],
			['/second/' => ['second' => 'second']],
			'anotherhandler',
			'',
			'isActive'
		);

		$rewriteCollection->add($one);
		$rewriteCollection->add($two);

		$this->assertSame([
			[
				'methods' => ['GET'],
				'rules' => ['/first/' => 'index.php?pfx_first=first'],
				'handler' => 'somehandler',
				'prefixedToUnprefixedQueryVariablesMap' => ['pfx_first' => 'first'],
				'queryVariables' => ['pfx_first'],
				'isActiveCallback' => null,
			],
			[
				'methods' => ['POST'],
				'rules' => ['/second/' => 'index.php?second=second'],
				'handler' => 'anotherhandler',
				'prefixedToUnprefixedQueryVariablesMap' => ['second' => 'second'],
				'queryVariables' => ['second'],
				'isActiveCallback' => 'isActive',
			],
		], (new RewriteCollectionDumper($rewriteCollection))->toArray());
	}
}
