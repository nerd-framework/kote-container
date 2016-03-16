<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.03.16
 * Time: 17:53
 */

namespace Kote\Container;


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

    /**
     * @param $class
     * @return array
     */
    public static function getClassSubclasses($class)
    {
        $result = [];

        $reflection = new \ReflectionClass($class);

        do {
            $result[] = $reflection->getName();
        } while ($reflection = $reflection->getParentClass());

        return $result;
    }
}