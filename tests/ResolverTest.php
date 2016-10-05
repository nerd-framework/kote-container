<?php

namespace tests;

use Nerd\Framework\Container\Container;
use PHPUnit\Framework\TestCase;
use tests\Tools\SomeType;

class ResolverTest extends TestCase
{
    public function testAddingResolver()
    {
        $container = new Container();

        $container->addResolver(function ($id) {
            return new SomeType($id);
        }, SomeType::class);

        $container->addResolver(function ($name) {
            return $name . "bar";
        }, "foo");

        $result1 = $container->invoke(function (SomeType $object) {
            return $object->id;
        });

        $result2 = $container->invoke(function ($foo) {
            return $foo;
        });

        $this->assertEquals("object", $result1);
        $this->assertEquals("foobar", $result2);
    }

    public function testResolverCache()
    {
        $container = new Container();

        $container->addResolver(function ($id) {
            return new SomeType($id);
        }, SomeType::class);

        $result1 = $container->invoke(function (SomeType $object) {
            return $object;
        });
        $result2 = $container->invoke(function (SomeType $object) {
            return $object;
        });

        $this->assertSame($result1, $result2);
    }
}
