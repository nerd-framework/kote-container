<?php

if (!function_exists("container"))
{
    /**
     * @param null $id
     * @return \Kote\Container\Container|object
     * @throws \Kote\Container\Exception\NotFoundException
     */
    function container($id = null)
    {
        if (!is_null($id)) {
            return container()->get($id);
        }

        static $instance = null;

        if (is_null($instance)) {
            $instance = new \Kote\Container\Container();
        }

        return $instance;
    }
}

if (!function_exists("not_null"))
{
    /**
     * @param $var
     * @return bool
     */
    function not_null($var)
    {
        return ! is_null($var);
    }
}