<?php

namespace ToyWpRouting\Tests;

use DI\Container;
use InvalidArgumentException;
use Invoker\Invoker;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use PHPUnit\Framework\TestCase;
use ToyWpRouting\AbstractRewrite;

use function DI\value;

class AbstractRewriteTest extends TestCase
{
	public function testIsActive()
	{
		$rewrite = $rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function() {
			return true;
		});

		$this->assertTrue($rewrite->isActive());

		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function() {
			return false;
		});

		$this->assertFalse($rewrite->isActive());
	}

	public function testIsActiveWithNoCallback()
	{
		$rewrite = new AbstractRewriteTester();

		$this->assertTrue($rewrite->isActive());
	}

	public function testIsActiveWithNonBooleanReturn()
	{
		// Supported, but not recommended.
		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function() {
			return 1;
		});

		$this->assertTrue($rewrite->isActive());

		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function() {
			return 0;
		});

		$this->assertFalse($rewrite->isActive());
	}

	public function testIsActiveWithNonCallableCallback()
	{
		$this->expectException(InvalidArgumentException::class);

		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(true);

		$rewrite->isActive();
	}

	public function testIsActiveWithInvoker()
	{
		$runCount = 0;

		$container = new Container();
		$container->set('truthy', true);
		$container->set('falsy', false);
		$container->set('callback', value(function() use (&$runCount) {
			$runCount++;
			return false;
		}));

		$invoker = new Invoker(new ParameterNameContainerResolver($container), $container);

		// Can resolve callable from container.
		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback('callback');

		$this->assertFalse($rewrite->isActive($invoker));
		$this->assertSame(1, $runCount);

		// Can provide container values as params to callback.
		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function($truthy) use (&$runCount) {
			$runCount++;

			return $truthy;
		});

		$this->assertTrue($rewrite->isActive($invoker));
		$this->assertSame(2, $runCount);

		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function($falsy) use (&$runCount) {
			$runCount++;

			return $falsy;
		});

		$this->assertFalse($rewrite->isActive($invoker));
		$this->assertSame(3, $runCount);
	}

	public function testIsActiveWithInvokerAndNonBooleanReturn()
	{
		$runCount = 0;

		$container = new Container();
		$container->set('truthy', 1);
		$container->set('falsy', 0);

		$invoker = new Invoker(new ParameterNameContainerResolver($container), $container);

		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function($truthy) use (&$runCount) {
			$runCount++;

			return $truthy;
		});

		$this->assertTrue($rewrite->isActive($invoker));
		$this->assertSame(1, $runCount);

		$rewrite = new AbstractRewriteTester();
		$rewrite->setIsActiveCallback(function($falsy) use (&$runCount) {
			$runCount++;

			return $falsy;
		});

		$this->assertFalse($rewrite->isActive($invoker));
		$this->assertSame(2, $runCount);
	}
}

class AbstractRewriteTester extends AbstractRewrite
{
	public function getHandler() { return 'somehandler'; }
	public function getMethod(): string { return 'GET'; }
	public function getPrefixedToUnprefixedQueryVariablesMap(): array { return ['pfx_var' => 'value']; }
	public function getQuery(): string { return 'index.php?pfx_var=value'; }
	public function getQueryVariables(): array { return ['pfx_var']; }
	public function getRegex(): string { return 'someregex'; }
}
