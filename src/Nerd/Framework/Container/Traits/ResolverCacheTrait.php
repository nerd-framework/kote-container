<?php

namespace Nerd\Framework\Container\Traits;

trait ResolverCacheTrait
{
    /**
     * Cache for resolved resources.
     *
     * @var array
     */
    private $resolversCache = [];

    /**
     * Retrieves data from resolvers cache.
     * Returns data on success or NULL if not found.
     *
     * @param string $id
     * @param string $type
     * @return null|object
     */
    protected function retrieveFromCache($id, $type)
    {
        if (isset($this->resolversCache[$type][$id])) {
            return $this->resolversCache[$type][$id];
        }
        return null;
    }

    /**
     * Stores data into resolvers cache.
     *
     * @param string $id
     * @param string $type
     * @param object $data
     * @return $this
     */
    protected function storeToCache($id, $type, $data)
    {
        if (!isset($this->resolversCache[$type])) {
            $this->resolversCache[$type] = [];
        }
        $this->resolversCache[$type][$id] = $data;

        return $this;
    }

    /**
     * @param $id
     * @param $type
     * @return bool
     */
    protected function cacheContains($id, $type)
    {
        return isset($this->resolversCache[$type][$id]);
    }
}
