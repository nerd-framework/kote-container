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
        if (isset($id)) {
            return container()->get($id);
        }

        static $instance = null;

        if (is_null($instance)) {
            $instance = new \Kote\Container\Container();
        }

        return $instance;
    }
}

