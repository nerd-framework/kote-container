<?php

namespace Nerd\Framework\Container\Contracts;

interface Container
{
    /**
     * @param string $id
     * @return bool
     */
    public function has($id);

    /**
     * @param string $id
     * @return object
     * @throws \Nerd\Framework\Container\Exceptions\NotFoundException
     */
    public function get($id);

    /**
     * @param string $id
     * @return $this
     */
    public function unbind($id);

    /**
     * @param string $id
     * @param null $provider
     * @return $this
     */
    public function bind($id, $provider = null);

    /**
     * @param string $id
     * @param null $provider
     * @return $this
     */
    public function singleton($id, $provider = null);

    /**
     * @param $callable
     * @param array $args
     * @return $this
     */
    public function invoke($callable, array $args = []);
}
