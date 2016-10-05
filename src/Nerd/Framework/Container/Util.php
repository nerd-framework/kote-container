<?php

namespace Nerd\Framework\Container;

class Util
{
    /**
     * @param \ReflectionParameter $parameter
     * @return string
     */
    public static function getTypeFromReflectionParameter(\ReflectionParameter $parameter)
    {
        $class = $parameter->getClass();

        return is_null($class) ? $parameter->getName() : $class->getName();
    }
}
