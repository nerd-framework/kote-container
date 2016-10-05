<?php

class ResolverTest extends PHPUnit_Framework_TestCase
{
    public function testAddingResolver()
    {
        $container = new \Nerd\Framework\Container\Container();

        $container->addResolver(function ($id) {

            return new SomeType($id);

        }, SomeType::class);

        $result = $container->invoke(function (SomeType $object) {

            return $object->id;

        });

        $this->assertEquals("object", $result);
    }

    public function testResolverCache()
    {
        $container = new \Nerd\Framework\Container\Container();

        $container->addResolver(function ($id) {

            return new SomeType($id);

        }, SomeType::class);

        $result1 = $container->invoke(function (SomeType $object) { return $object; });
        $result2 = $container->invoke(function (SomeType $object) { return $object; });

        $this->assertSame($result1, $result2);
    }
}

class SomeType
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
