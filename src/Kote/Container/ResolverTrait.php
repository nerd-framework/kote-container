<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.03.2016
 * Time: 14:00
 */

namespace Kote\Container;


trait ResolverTrait
{
    private $resolvers = [];

    private $resolvedCache = [];

    public function addResolver($callback)
    {
        $this->resolvers[] = $callback;
    }

    public function resolve($id)
    {
        if (isset($this->resolvedCache[$id])) {
            return $this->resolvedCache[$id];
        }

        foreach ($this->resolvers as $resolver) {
            if (!is_null($result = $resolver($id))) {
                return $this->resolvedCache[$id] = $result;
            }
        }

        throw new Exception\NotFoundException("Resource $id could not be resolved.");
    }

    public function isResolvable($id)
    {
        try {
            $this->resolve($id);
            return true;
        } catch (Exception\NotFoundException $exception) {
            return false;
        }

    }
}