<?php

namespace tests;

use Nerd\Framework\Container\Container;
use Nerd\Framework\Container\Exceptions\ContainerException;
use Nerd\Framework\Container\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;
use tests\Tools\FooBar;

class BadDayTest extends TestCase
{
    public function testThatExceptionOccursWhenTryToGetThatNotExists()
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->get('foo');
    }

    public function testThatExceptionOccursWhenInvokedFunctionUsesThatNotExists()
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->invoke(function ($something) {
        });
    }

    public function testThatExceptionOccursWhenResolverFailed()
    {
        $this->expectException(NotFoundException::class);

        $container = new Container();
        $container->addResolver(function () {
            return null;
        }, 'something');
        $container->invoke(function ($something) {
        });
    }

    public function testTryToUseClassNameAsSessionId()
    {
        $this->expectException(ContainerException::class);

        $container = new Container();
        $container->bind(FooBar::class, 'foobar');
    }
}
