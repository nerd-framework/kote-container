<?php

namespace Nerd\Framework\Container\Traits;

use Nerd\Framework\Container\Exceptions\NotFoundException;

trait ResolverTrait
{
    use ResolverCacheTrait;

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
     * @throws \Nerd\Framework\Container\Exceptions\NotFoundException
     */
    protected function resolve($id, $type = null)
    {
        $result = $this->retrieveFromCache($type, $id);

        if (isset($result)) {
            return $result;
        }

        if (array_key_exists($type, $this->resolvers)) {
            foreach ($this->resolvers[$type] as $resolver) {
                if (!is_null($result = $resolver($id, $this))) {
                    $this->storeToCache($type, $id, $result);
                    return $result;
                }
            }
        }

        throw new NotFoundException("Resource $id with type $type could not be resolved.");
    }

    /**
     * @param $id
     * @param null $type
     * @return bool
     */
    protected function isResolvable($id, $type = null)
    {
        try {
            $this->resolve($id, $type);
            return true;
        } catch (NotFoundException $exception) {
            return false;
        }
    }
}
