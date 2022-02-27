<?php

namespace ToyWpRouting\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpRouting\OptimizedRewrite;

class OptimizedRewriteTest extends TestCase
{
	public function testGetters()
	{
		$rewrite = new OptimizedRewrite(
			['GET'],
			['someregex' => 'index.php?pfx_var=value'],
			'somehandler',
			['pfx_var' => 'var'],
			['pfx_var'],
			'isActiveCallback'
		);

		$this->assertSame(['someregex' => 'index.php?pfx_var=value'], $rewrite->getRules());
		$this->assertSame(['GET'], $rewrite->getMethods());
		$this->assertSame('somehandler', $rewrite->getHandler());
		$this->assertSame(['pfx_var'], $rewrite->getQueryVariables());
		$this->assertSame(
			['pfx_var' => 'var'],
			$rewrite->getPrefixedToUnprefixedQueryVariablesMap()
		);
		$this->assertSame('isActiveCallback', $rewrite->getIsActiveCallback());
	}
}
