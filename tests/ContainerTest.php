<?php

class ContainerTest extends PHPUnit_Framework_TestCase
{
    public function testContainerInstantiation()
    {
        $container = new \Nerd\Framework\Container\Container();

        $this->assertInstanceOf(\Nerd\Framework\Container\Container::class, $container);
    }

    public function testMainContainerFlow()
    {
        $container = new \Nerd\Framework\Container\Container();

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

        $container = new \Nerd\Framework\Container\Container();

        $container->get('somethingThatDoesNotExist');
    }

    public function testAddingSingleton()
    {
        $counter = 0;

        $container = new \Nerd\Framework\Container\Container();

        $container->singleton('foo', FooBar::class);
        $container->singleton('bar', function () use (&$counter) { return ++ $counter; });

        $foo1 = $container->get('foo');
        $foo2 = $container->get('foo');

        $this->assertInstanceOf(FooBar::class, $foo1);
        $this->assertInstanceOf(FooBar::class, $foo2);

        $this->assertSame($foo1, $foo2);

        $bar1 = $container->get('bar');
        $bar2 = $container->get('bar');

        $this->assertEquals(1, $bar1);
        $this->assertEquals(1, $bar2);
    }

    public function testAddingFactory()
    {
        $counter = 0;

        $container = new \Nerd\Framework\Container\Container();

        $container->factory('foo', FooBar::class);
        $container->factory('bar', function () use (&$counter) { return ++ $counter; });

        $foo1 = $container->get('foo');
        $foo2 = $container->get('foo');

        $this->assertInstanceOf(FooBar::class, $foo1);
        $this->assertInstanceOf(FooBar::class, $foo2);

        $this->assertNotSame($foo1, $foo2);

        $bar1 = $container->get('bar');
        $bar2 = $container->get('bar');

        $this->assertEquals(1, $bar1);
        $this->assertEquals(2, $bar2);
    }

    public function testAddingBindings()
    {
        $container = new \Nerd\Framework\Container\Container();

        $foobar = new FooBar();

        $container->bind('foobar', $foobar);

        $this->assertSame($foobar, $container->get('foobar'));
    }

    public function testDependencyInjection()
    {
        $container = new \Nerd\Framework\Container\Container();

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
        $container = new \Nerd\Framework\Container\Container();

        $container::setInstance($container);
    }

    public function testGettingGlobalInstance()
    {
        $container = \Nerd\Framework\Container\Container::getInstance();

        $this->assertInstanceOf(\Nerd\Framework\Container\Container::class, $container);
    }
}


class FooBar
{
    //
}

class HelloWorld
{
    private $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
