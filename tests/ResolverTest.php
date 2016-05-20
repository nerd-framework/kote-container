<?php

/**
 * @author Roman Gemini <roman_gemini@ukr.net>
 * @date 20.05.2016
 * @time 16:27
 */
class ResolverTest extends PHPUnit_Framework_TestCase
{
    public function testAddingResolver()
    {
        $container = new \Kote\Container\Container();

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
        $container = new \Kote\Container\Container();

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