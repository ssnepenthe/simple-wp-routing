<?php

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\OptimizedRewrite;

class OptimizedRewriteTest extends TestCase
{
	public function testGetters()
	{
		$rewrite = new OptimizedRewrite(
			'GET',
			'someregex',
			'somehandler',
			['pfx_var' => 'var'],
			'index.php?pfx_var=value',
			['pfx_var']
		);

		$this->assertSame('somehandler', $rewrite->getHandler());
		$this->assertSame('GET', $rewrite->getMethod());
		$this->assertSame(
			['pfx_var' => 'var'],
			$rewrite->getPrefixedToUnprefixedQueryVariablesMap()
		);
		$this->assertSame('index.php?pfx_var=value', $rewrite->getQuery());
		$this->assertSame(['pfx_var'], $rewrite->getQueryVariables());
		$this->assertSame('someregex', $rewrite->getRegex());
	}
}
