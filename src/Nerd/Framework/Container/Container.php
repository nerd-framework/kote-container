<?php

namespace Nerd\Framework\Container;

use Nerd\Framework\Container\Exceptions\NotFoundException;

class Container implements ContainerContract
{
    use Traits\ResolverTrait;

    /**
     * Storage for all registered services.
     *
     * @var callable[]
     */
    private $storage = [];

    /**
     * Check whether service exists in container.
     *
     * @param $id
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->storage);
    }

    /**
     * @param $id
     * @return object
     * @throws \Nerd\Framework\Container\Exceptions\NotFoundException
     */
    public function get($id)
    {
        if (!self::has($id)) {
            throw new NotFoundException("Service \"$id\" not found in container.");
        }

        return call_user_func($this->storage[$id]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function unbind($id)
    {
        if (self::has($id)) {
            unset($this->storage[$id]);
        }

        return $this;
    }

    /**
     * Bind resource to given service id.
     *
     * @param string $id
     * @param mixed $resource
     * @return $this
     */
    public function bind($id, $resource)
    {
        $this->storage[$id] = function () use ($resource) {
            return $resource;
        };

        return $this;
    }

    /**
     * Register singleton service.
     *
     * @param string $id
     * @param null $provider
     * @return $this
     */
    public function singleton($id, $provider = null)
    {
        if (is_null($provider)) {
            $provider = $id;
        }

        $this->storage[$id] = function () use ($provider) {
            static $instance = null;

            if (is_null($instance)) {
                $instance = $this->invoke($provider);
            }

            return $instance;
        };

        return $this;
    }

    /**
     * Register factory service.
     *
     * @param string $id
     * @param null $provider
     * @return $this
     */
    public function factory($id, $provider = null)
    {
        if (is_null($provider)) {
            $provider = $id;
        }

        $this->storage[$id] = function () use ($provider) {
            return $this->invoke($provider);
        };

        return $this;
    }

    /**
     * @param $callable
     * @param array $args
     * @return $this
     */
    public function invoke($callable, array $args = [])
    {
        $isClassMethod = function ($callable) {
            return is_array($callable) && count($callable) == 2;
        };
        $isClassName = function ($callable) {
            return is_string($callable) && class_exists($callable);
        };

        if ($isClassMethod($callable)) {
            return $this->invokeClassMethod($callable[0], $callable[1], $args);
        }

        if ($isClassName($callable)) {
            return $this->invokeClassConstructor($callable, $args);
        }

        return $this->invokeFunction($callable, $args);
    }

    /**
     * @param $function
     * @param array $args
     * @return mixed
     */
    private function invokeFunction($function, array $args = [])
    {
        $reflection = new \ReflectionFunction($function);

        $dependencies = iterator_to_array($this->getDependencies($reflection->getParameters(), $args));

        return $function(...$dependencies);
    }

    private function invokeClassConstructor($class, array $args = [])
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $class;
        }

        $dependencies = iterator_to_array($this->getDependencies($constructor->getParameters(), $args));

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
        $dependencies = iterator_to_array($this->getDependencies($function->getParameters(), $args));

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
     * @return \Generator
     * @throws NotFoundException
     */
    private function getDependencies(array $parameters, array $args = [])
    {
        foreach ($parameters as $parameter) {
            yield $this->loadDependency($parameter, $args);
        }
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param array $args
     * @return object
     * @throws NotFoundException
     */
    private function loadDependency(\ReflectionParameter $parameter, array $args)
    {
        $name = $parameter->getName();
        $class = $parameter->getClass();

        if (array_key_exists($name, $args)) {
            return $args[$name];
        }

        if (isset($class) && $this->has($class->getName())) {
            return $this->get($class->getName());
        }

        if ($this->has($name)) {
            return $this->get($name);
        }

        $type = isset($class) ? $class->getName() : null;

        if ($this->isResolvable($name, $type)) {
            return $this->resolve($name, $type);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new NotFoundException("Service \"{$parameter->getName()}\" not found in container.");
    }
}
