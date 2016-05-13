<?php

if (!function_exists("app"))
{
    /**
     * @param null $id
     * @return \Kote\Container\Container|object
     * @throws \Kote\Container\Exception\NotFoundException
     */
    function app($id = null)
    {
        if (isset($id)) {
            return app()->get($id);
        }

        static $instance = null;

        if (is_null($instance)) {
            $instance = new \Kote\Container\Container();
        }

        return $instance;
    }
}

