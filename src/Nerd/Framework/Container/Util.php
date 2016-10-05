<?php

namespace Nerd\Framework\Container;

class Util
{
    /**
     * @param \ReflectionParameter $parameter
     * @return null|string
     */
    public static function getTypeFromReflectionParameter(\ReflectionParameter $parameter)
    {
        if (is_null($class = $parameter->getClass())) {
            return null;
        }

        return $class->getName();
    }
}
