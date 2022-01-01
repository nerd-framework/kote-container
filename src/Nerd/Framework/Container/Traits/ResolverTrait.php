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
     * @param string   $type
     *
     * @return $this
     */
    public function addResolver($callback, $type = '')
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
     * @param string $type
     *
     * @throws \Nerd\Framework\Container\Exceptions\NotFoundException
     *
     * @return null|object
     */
    protected function resolve($id, $type = '')
    {
        $result = $this->retrieveFromCache($id, $type);

        if (isset($result)) {
            return $result;
        }

        if (array_key_exists($type, $this->resolvers)) {
            foreach ($this->resolvers[$type] as $resolver) {
                $result = $resolver($id, $this);
                if (!is_null($result)) {
                    $this->storeToCache($id, $type, $result);

                    return $result;
                }
            }
        }

        throw new NotFoundException("Item \"$id\" of type \"$type\" could not be resolved.");
    }

    /**
     * @param $id
     * @param null $type
     *
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
