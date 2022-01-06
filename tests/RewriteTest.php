<?php

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\Rewrite;

class RewriteTest extends TestCase
{
	public function testGetters()
	{
		$rewrite = new Rewrite('GET', 'someregex', ['var' => 'value'], 'somehandler');

		$this->assertSame('somehandler', $rewrite->getHandler());
		$this->assertSame('GET', $rewrite->getMethod());
		$this->assertSame(['var' => 'var'], $rewrite->getPrefixedToUnprefixedQueryVariablesMap());
		$this->assertSame('index.php?var=value', $rewrite->getQuery());
		$this->assertSame(['var'], $rewrite->getQueryVariables());
		$this->assertSame('someregex', $rewrite->getRegex());
	}

	public function testGettersWithPrefix()
	{
		$rewrite = new Rewrite('GET', 'someregex', ['var' => 'value'], 'somehandler', 'pfx_');

		$this->assertSame('somehandler', $rewrite->getHandler());
		$this->assertSame('GET', $rewrite->getMethod());
		$this->assertSame(['pfx_var' => 'var'], $rewrite->getPrefixedToUnprefixedQueryVariablesMap());
		$this->assertSame('index.php?pfx_var=value', $rewrite->getQuery());
		$this->assertSame(['pfx_var'], $rewrite->getQueryVariables());
		$this->assertSame('someregex', $rewrite->getRegex());
	}

	// public function testOptimize()
	// {
	// 	$optimized = (new Rewrite('GET', 'someregex', ['var' => 'value'], 'somehandler'))
	// 		->optimize();

	// 	$this->assertInstanceOf(OptimizedRewrite::class, $optimized);

	// 	$this->assertSame('somehandler', $optimized->getHandler());
	// 	$this->assertSame('GET', $optimized->getMethod());
	// 	$this->assertSame(['var' => 'var'], $optimized->getPrefixedToUnprefixedQueryVariablesMap());
	// 	$this->assertSame('index.php?var=value', $optimized->getQuery());
	// 	$this->assertSame(['var'], $optimized->getQueryVariables());
	// 	$this->assertSame('someregex', $optimized->getRegex());
	// }

	// public function testOptimizeWithPrefix()
	// {
	// 	$optimized = (new Rewrite('GET', 'someregex', ['var' => 'value'], 'somehandler', 'pfx_'))
	// 		->optimize();

	// 	$this->assertInstanceOf(OptimizedRewrite::class, $optimized);

	// 	$this->assertSame('somehandler', $optimized->getHandler());
	// 	$this->assertSame('GET', $optimized->getMethod());
	// 	$this->assertSame(
	// 		['pfx_var' => 'var'],
	// 		$optimized->getPrefixedToUnprefixedQueryVariablesMap()
	// 	);
	// 	$this->assertSame('index.php?pfx_var=value', $optimized->getQuery());
	// 	$this->assertSame(['pfx_var'], $optimized->getQueryVariables());
	// 	$this->assertSame('someregex', $optimized->getRegex());
	// }

	// public function testOptimizeWithIsActiveCallback()
	// {
	// 	$optimized = (new Rewrite('GET', 'someregex', ['var' => 'value'], 'somehandler'))->optimize();

	// 	$this->assertTrue($optimized->isActive());

	// 	$rewrite = new Rewrite('GET', 'someregex', ['var' => 'value'], 'somehandler');
	// 	$rewrite->setIsActiveCallback(function() { return true; });
	// 	$optimized = $rewrite->optimize();

	// 	$this->assertTrue($optimized->isActive());

	// 	$rewrite = new Rewrite('GET', 'someregex', ['var' => 'value'], 'somehandler');
	// 	$rewrite->setIsActiveCallback(function() { return true; });
	// 	$optimized = $rewrite->optimize();

	// 	$this->assertTrue($optimized->isActive());
	// }
}
