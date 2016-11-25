<?php

namespace tests;

use Nerd\Framework\Container\Container;
use PHPUnit\Framework\TestCase;
use tests\Tools\FooBar;

class BadDayTest extends TestCase
{
    /**
     * @expectedException \Nerd\Framework\Container\Exceptions\NotFoundException
     */
    public function testThatExceptionOccursWhenTryToGetThatNotExists()
    {
        $container = new Container();
        $container->get('foo');
    }

    /**
     * @expectedException \Nerd\Framework\Container\Exceptions\NotFoundException
     */
    public function testThatExceptionOccursWhenInvokedFunctionUsesThatNotExists()
    {
        $container = new Container();
        $container->invoke(function ($something) {
        });
    }

    /**
     * @expectedException \Nerd\Framework\Container\Exceptions\NotFoundException
     */
    public function testThatExceptionOccursWhenResolverFailed()
    {
        $container = new Container();
        $container->addResolver(function () {
            return null;
        }, 'something');
        $container->invoke(function ($something) {
        });
    }

    /**
     * @expectedException \Nerd\Framework\Container\Exceptions\ContainerException
     */
    public function testTryToUseClassNameAsSessionId()
    {
        $container = new Container();
        $container->bind(FooBar::class, 'foobar');
    }
}
