<?php

namespace tests;

use Nerd\Framework\Container\Container;
use PHPUnit\Framework\TestCase;
use tests\Tools\FooBar;
use tests\Tools\HelloWorld;

class ContainerTest extends TestCase
{
    public function testContainerInstantiation()
    {
        $container = new Container();

        $this->assertInstanceOf(Container::class, $container);
    }

    public function testMainContainerFlow()
    {
        $container = new Container();

        $this->assertFalse($container->has('foo'));

        $container->bind('foo', 'bar');

        $this->assertTrue($container->has('foo'));

        $bar = $container->get('foo');

        $this->assertEquals('bar', $bar);

        $container->unbind('foo');

        $this->assertFalse($container->has('foo'));
    }

    /**
     * @expectedException \Nerd\Framework\Container\Exceptions\NotFoundException
     */
    public function testResourceNotFound()
    {
        $this->setExpectedExceptionFromAnnotation();

        $container = new Container();

        $container->get('somethingThatDoesNotExist');
    }

    public function testAddingSingleton()
    {
        $counter = 0;

        $container = new Container();

        $container->singleton('foo', FooBar::class);
        $container->singleton('bar', function () use (&$counter) {
            return ++$counter;
        });
        $container->singleton(FooBar::class);

        $foo1 = $container->get('foo');
        $foo2 = $container->get('foo');
        $foo3 = $container->get(FooBar::class);

        $this->assertInstanceOf(FooBar::class, $foo1);
        $this->assertInstanceOf(FooBar::class, $foo2);
        $this->assertInstanceOf(FooBar::class, $foo3);

        $this->assertSame($foo1, $foo2);

        $bar1 = $container->get('bar');
        $bar2 = $container->get('bar');

        $this->assertEquals(1, $bar1);
        $this->assertEquals(1, $bar2);
    }

    public function testAddingFactory()
    {
        $counter = 0;

        $container = new Container();

        $container->factory('foo', FooBar::class);
        $container->factory('bar', function () use (&$counter) {
            return ++$counter;
        });
        $container->factory(FooBar::class);

        $foo1 = $container->get('foo');
        $foo2 = $container->get('foo');
        $foo3 = $container->get(FooBar::class);

        $this->assertInstanceOf(FooBar::class, $foo1);
        $this->assertInstanceOf(FooBar::class, $foo2);
        $this->assertInstanceOf(FooBar::class, $foo3);

        $this->assertNotSame($foo1, $foo2);

        $bar1 = $container->get('bar');
        $bar2 = $container->get('bar');

        $this->assertEquals(1, $bar1);
        $this->assertEquals(2, $bar2);
    }

    public function testAddBindings()
    {
        $container = new Container();

        $foobar = new FooBar();

        $container->bind('foobar', $foobar);

        $this->assertSame($foobar, $container->get('foobar'));
    }

    public function testDependencyInjection()
    {
        $container = new Container();

        $container->bind('foo', 'bar');
        $container->bind('hello', 'world');

        $args = ['temp' => 'baz'];

        $result = $container->invoke(function ($foo, $hello, $temp) {
            return "$foo-$hello-$temp";
        }, $args);

        $this->assertEquals('bar-world-baz', $result);

        /**
         * @var HelloWorld $helloWorld
         */
        
        $helloWorld = $container->invoke(HelloWorld::class);

        $this->assertInstanceOf(HelloWorld::class, $helloWorld);

        $this->assertEquals('bar', $helloWorld->getFoo());
    }

    public function testSettingGlobalInstance()
    {
        $container = new Container();

        $container::setInstance($container);
    }

    public function testGettingGlobalInstance()
    {
        $container = Container::getInstance();

        $this->assertInstanceOf(Container::class, $container);
    }

    public function testArgumentDefaultValue()
    {
        $container = new Container();
        $result = $container->invoke(function ($foo = "bar") {
            return $foo;
        });
        $this->assertEquals("bar", $result);
    }

    public function testClassConstructorAndMethodCall()
    {
        $container = new Container();

        $container->bind('foo', 'bar');
        $container->factory(HelloWorld::class);

        $bar = $container->invoke(FooBar::class);

        list($helloWorld, $foo, $other) = $container->invoke([$bar, "call"]);

        $this->assertInstanceOf(HelloWorld::class, $helloWorld);
        $this->assertEquals('bar', $foo);
        $this->assertEquals($other, 10);

        $static = $container->invoke([FooBar::class, 'callStatic']);
        $instance = $container->invoke([FooBar::class, 'callInstance']);

        $this->assertEquals('static', $static);
        $this->assertEquals('instance', $instance);
    }
}
