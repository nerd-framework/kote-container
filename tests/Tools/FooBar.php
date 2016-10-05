<?php

namespace tests\Tools;

class FooBar
{
    public function call(HelloWorld $hello, $foo, $other = 10)
    {
        return [$hello, $foo, $other];
    }

    public static function callStatic()
    {
        return 'static';
    }

    public function callInstance()
    {
        return 'instance';
    }
}
