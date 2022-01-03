<?php

namespace Nerd\Framework\Container;

use Nerd\Framework\Container\Exceptions\ContainerException;
use Nerd\Framework\Container\Exceptions\NotFoundException;
use ReflectionException;
use ReflectionMethod;

class Container implements ContainerContract
{
    use Traits\ResolverTrait;

    /**
     * Storage of all registered services.
     *
     * @var callable[][]
     */
    private $storage = [];

    /**
     * Storage of class name service aliases.
     *
     * @var array
     */
    private $classAliases = [];

    /**
     * @param $serviceId
     * @param $factory
     */
    private function addFactory($serviceId, $factory)
    {
        if (!array_key_exists($serviceId, $this->storage)) {
            $this->storage[$serviceId] = [];
        }

        array_push($this->storage[$serviceId], $factory);
    }

    /**
     * Binds class name alias to service id.
     *
     * @param $serviceId
     * @param $classAlias
     *
     * @throws ContainerException
     *
     * @return $this
     */
    public function alias($serviceId, $classAlias)
    {
        if (!class_exists($classAlias) && !interface_exists($classAlias)) {
            throw new ContainerException("Class \"$classAlias\" does not exist.");
        }

        if (!$this->has($serviceId)) {
            throw new NotFoundException(
                "Could not bind class \"$classAlias\" alias to service \"$serviceId\" that does not exist."
            );
        }

        $this->classAliases[$classAlias] = $serviceId;

        return $this;
    }

    /**
     * Check whether class name alias exists in container.
     *
     * @param $classAlias
     *
     * @return bool
     */
    public function hasAlias($classAlias)
    {
        return array_key_exists($classAlias, $this->classAliases);
    }

    /**
     * @param $classAlias
     *
     * @throws NotFoundException
     *
     * @return object
     */
    public function getAlias($classAlias)
    {
        if ($this->hasAlias($classAlias)) {
            $serviceId = $this->classAliases[$classAlias];

            return $this->get($serviceId);
        }

        throw new NotFoundException("Class alias \"$classAlias\" not found in container.");
    }

    /**
     * @param $serviceId
     *
     * @throws ContainerException
     */
    private function validateServiceId($serviceId)
    {
        if (class_exists($serviceId)) {
            throw new ContainerException(
                'Do not use class name as service id directly. Use class name alias instead.'
            );
        }
    }

    /**
     * Check whether service exists in container.
     *
     * @param $serviceId
     *
     * @return bool
     */
    public function has($serviceId)
    {
        return array_key_exists($serviceId, $this->storage);
    }

    /**
     * Check how many services are bound to given service id in container.
     *
     * @param $serviceId
     *
     * @return bool
     */
    public function count($serviceId)
    {
        return count($this->storage[$serviceId] ?: []);
    }

    /**
     * @param $serviceId
     *
     * @throws NotFoundException
     * @throws ContainerException
     *
     * @return object
     */
    public function get($serviceId)
    {
        if (!$this->has($serviceId)) {
            throw new NotFoundException("Service \"$serviceId\" not found in container.");
        }

        if ($this->count($serviceId) > 1) {
            throw new ContainerException("More than one service bound to \"$serviceId\" in container.");
        }

        return call_user_func($this->storage[$serviceId][0]);
    }

    /**
     * @param $serviceId
     *
     * @throws NotFoundException
     *
     * @return object[]
     */
    public function getAll($serviceId)
    {
        if (!$this->has($serviceId)) {
            throw new NotFoundException("Service \"$serviceId\" not found in container.");
        }

        return array_map(function ($factory) {
            return call_user_func($factory);
        }, $this->storage[$serviceId]);
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function unbind($id)
    {
        if ($this->has($id)) {
            unset($this->storage[$id]);
        }

        return $this;
    }

    /**
     * Bind resource to given service id.
     *
     * @param string $serviceId
     * @param mixed  $resource
     *
     * @return $this
     */
    public function bind($serviceId, $resource)
    {
        $this->validateServiceId($serviceId);

        $this->addFactory($serviceId, function () use ($resource) {
            return $resource;
        });

        return $this;
    }

    /**
     * Register singleton service.
     *
     * @param string $serviceId
     * @param mixed  $provider
     *
     * @return $this
     */
    public function singleton($serviceId, $provider)
    {
        $this->validateServiceId($serviceId);

        $this->addFactory($serviceId, function () use ($provider) {
            static $instance = null;

            if (is_null($instance)) {
                $instance = $this->invoke($provider);
            }

            return $instance;
        });

        return $this;
    }

    /**
     * Register factory service.
     *
     * @param string $serviceId
     * @param mixed  $provider
     *
     * @return $this
     */
    public function factory($serviceId, $provider)
    {
        $this->validateServiceId($serviceId);

        $this->addFactory($serviceId, function () use ($provider) {
            return $this->invoke($provider);
        });

        return $this;
    }

    /**
     * @param $callable
     * @param array $args
     *
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
     *
     * @throws ReflectionException|NotFoundException
     *
     * @return mixed
     */
    private function invokeFunction($function, array $args = [])
    {
        $reflection = new \ReflectionFunction($function);
        $dependencies = $this->getDependencies($reflection->getParameters(), $args);
        $dependenciesArray = iterator_to_array($dependencies);

        return $function(...$dependenciesArray);
    }

    /**
     * @param $class
     * @param array $args
     *
     * @return mixed
     */
    private function invokeClassConstructor($class, array $args = [])
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            return new $class();
        }

        $dependencies = $this->getDependencies($constructor->getParameters(), $args);
        $dependenciesArray = iterator_to_array($dependencies);

        return new $class(...$dependenciesArray);
    }

    /**
     * @param $class
     * @param $method
     * @param array $args
     *
     * @throws ReflectionException
     *
     * @return mixed
     */
    private function invokeClassMethod($class, $method, array $args = [])
    {
        $function = new ReflectionMethod($class, $method);
        $dependencies = $this->getDependencies($function->getParameters(), $args);
        $dependenciesArray = iterator_to_array($dependencies);

        if ($function->isStatic()) {
            return $class::$method(...$dependenciesArray);
        }

        if (is_string($class) && !$function->isStatic()) {
            $class = $this->invokeClassConstructor($class, $args);
        }

        return $class->$method(...$dependenciesArray);
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param array                  $args
     *
     * @throws NotFoundException
     *
     * @return \Generator
     */
    private function getDependencies(array $parameters, array $args = [])
    {
        foreach ($parameters as $index => $parameter) {
            yield $this->loadDependency($parameter, $args, $index);
        }
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param array                $args
     * @param int                  $parameterIndex
     *
     * @throws ContainerException|ReflectionException|NotFoundException
     *
     * @return object
     */
    private function loadDependency(\ReflectionParameter $parameter, array $args = [], $parameterIndex = 0)
    {
        $name = $parameter->getName();
        $class = $parameter->getClass();

        if (array_key_exists($name, $args)) {
            return $args[$name];
        }

        if (array_key_exists($parameterIndex, $args)) {
            return $args[$parameterIndex];
        }

        if (isset($class) && $this->hasAlias($class->getName())) {
            return $this->getAlias($class->getName());
        }

        if ($this->has($name)) {
            return $this->get($name);
        }

        $type = isset($class) ? $class->getName() : '';

        if ($this->isResolvable($name, $type)) {
            return $this->resolve($name, $type);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new NotFoundException("Dependency \"{$parameter->getName()}\" could not be injected.");
    }
}
