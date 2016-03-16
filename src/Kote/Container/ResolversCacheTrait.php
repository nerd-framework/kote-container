<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.03.16
 * Time: 17:58
 */

namespace Kote\Container;


trait ResolversCacheTrait {

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
     * @param string|null $type
     * @param string $id
     * @return null|object
     */
    protected function retrieveFromCache($type, $id)
    {
        if (isset($this->resolversCache[$type][$id])) {
            return $this->resolversCache[$type][$id];
        }
        return null;
    }

    /**
     * Stores data into resolvers cache.
     *
     * @param null|string $type
     * @param string $id
     * @param object $data
     * @return $this
     */
    protected function storeToCache($type, $id, $data)
    {
        if (!isset($this->resolversCache[$type])) {
            $this->resolversCache[$type] = [];
        }
        $this->resolversCache[$type][$id] = $data;

        return $this;
    }

}