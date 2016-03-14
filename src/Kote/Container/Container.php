<?php

namespace Kote\Container;


class Container
{
    /**
     * Storage for all registered services.
     *
     * @var array
     */
    private $storage = [];

    public function __construct()
    {

    }

    public function has($id)
    {
        return isset($this->storage[$id]);
    }

    /**
     * @param $id
     * @return object
     * @throws Exception\NotFoundException
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new Exception\NotFoundException("Resource \"$id\" not found in container.");
        }

        elseif (is_callable($this->storage[$id])) {
            return call_user_func($this->storage[$id]);
        }

        else {
            return $this->storage[$id];
        }
    }

    public function unbind($id)
    {
        if ($this->has($id)) {
            unset ($this->storage[$id]);
        }

        return $this;
    }

    public function bind($id, $provider)
    {
        $this->storage[$id] = $provider;

        return $this;
    }

    public function singleton($id, $provider)
    {
        $this->storage[$id] = function () use ($provider)
        {
            static $instance = null;

            if (is_null($instance)) {
                $instance = $this->invoke($provider);
            }

            return $instance;
        };

        return $this;
    }

    public function invoke($callable, array $args = [])
    {
        if (is_array($callable) && count($callable) == 2) {
            return $this->invokeClassMethod($callable[0], $callable[1], $args);
        }

        if (is_string($callable) && class_exists($callable)) {
            return $this->invokeClassConstructor($callable, $args);
        }

        return $this->invokeFunction($callable, $args);
    }

    private function invokeFunction($function, array $args = [])
    {
        $reflection = new \ReflectionFunction($function);

        $dependencies = $this->getDependencies($reflection->getParameters(), $args);

        return $function(...$dependencies);
    }

    private function invokeClassConstructor($class, array $args = [])
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $class;
        }

        $dependencies = $this->getDependencies($constructor->getParameters(), $args);

        return new $class(...$dependencies);
    }

    /**
     * @param $class
     * @param $method
     * @param array $args
     * @return mixed
     */
    private function invokeClassMethod($class, $method, array $args = [])
    {
        $function = new \ReflectionMethod($class, $method);
        $dependencies = $this->getDependencies($function->getParameters(), $args);

        if ($function->isStatic()) {
            return $class::$method(...$dependencies);
        }

        if (is_string($class) && !$function->isStatic()) {
            $class = $this->invokeClassConstructor($class, $args);
        }

        return $class->$method(...$dependencies);
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param array $args
     * @return object[]
     * @throws Exception\NotFoundException
     */
    private function getDependencies(array $parameters, array $args = [])
    {
        $instances = [];
        foreach ($parameters as $index => $parameter) {
            $instances[] = $this->loadDependency($parameter, $index, $args);
        }

        return $instances;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param $index
     * @param array $args
     * @return object
     * @throws Exception\NotFoundException
     */
    private function loadDependency(\ReflectionParameter $parameter, $index, array $args)
    {
        if (isset($args[$parameter->getName()])) {
            return $args[$parameter->getName()];
        }

        elseif (isset($args[$index])) {
            return $args[$index];
        }

        elseif (!is_null($parameter->getClass()) && $this->has($parameter->getClass()->getName())) {
            return $this->get($parameter->getClass()->getName());
        }

        elseif ($this->has($parameter->getName())) {
            return $this->get($parameter->getName());
        }

        elseif ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        else {
            throw new Exception\NotFoundException("Object with id {$parameter->getName()} not found in container.");
        }
    }
}