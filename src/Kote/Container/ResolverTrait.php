<?php

namespace Kote\Container;

trait ResolverTrait
{
    use ResolversCacheTrait;

    /**
     * Array where resolvers stored.
     *
     * @var array
     */
    private $resolvers = [];

    /**
     * Adds resolver to container resolvers array.
     *
     * @param null|string $type
     * @param callable $callback
     * @return $this
     */
    public function addResolver($type = null, $callback)
    {
        if (!isset($this->resolvers[$type])) {
            $this->resolvers[$type] = [];
        }
        $this->resolvers[$type][] = $callback;

        return $this;
    }

    /**
     * Resolves resource using resolvers.
     *
     * @param null|string $type
     * @param string $id
     * @return null|object
     * @throws Exception\NotFoundException
     */
    public function resolve($type = null, $id)
    {
        if ($result = $this->retrieveFromCache($type, $id)) {
            return $result;
        }

        if (isset($this->resolvers[$type])) {
            foreach ($this->resolvers[$type] as $resolver) {
                if (!is_null($result = $resolver($id, $type, $this))) {
                    $this->storeToCache($type, $id, $result);
                    return $result;
                }
            }
        }

        throw new Exception\NotFoundException("Resource $id with type $type could not be resolved.");
    }

    /**
     * @param null $type
     * @param $id
     * @return bool
     */
    public function isResolvable($type = null, $id)
    {
        try
        {
            $this->resolve($type, $id);
            return true;
        }
        catch (Exception\NotFoundException $exception)
        {
            // NOP
        }

        return false;
    }
}