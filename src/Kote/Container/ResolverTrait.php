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
     * Adds resolver to resolvers array.
     *
     * @param callable $callback
     * @param null|string $type
     * @return $this
     */
    public function addResolver($callback, $type = null)
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
     * @param string $id
     * @param null|string $type
     * @return null|object
     * @throws Exception\NotFoundException
     */
    public function resolve($id, $type = null)
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
     * @param $id
     * @param null $type
     * @return bool
     */
    public function isResolvable($id, $type = null)
    {
        try
        {
            $this->resolve($id, $type);
            return true;
        }
        catch (Exception\NotFoundException $exception)
        {
            // NOP
        }

        return false;
    }
}