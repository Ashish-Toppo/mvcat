<?php

namespace Core\Classes\Relations;

use Core\Classes\Model;

abstract class Relation
{
    protected Model $query;
    protected Model $parent;

    public function __construct(Model $query, Model $parent)
    {
        $this->query = $query;
        $this->parent = $parent;

        $this->addConstraints();
    }

    abstract protected function addConstraints(): void;
    abstract public function addEagerConstraints(array $models): void;
    abstract public function initRelation(array $models, string $relation): array;
    abstract public function match(array $models, array $results, string $relation): array;
    abstract public function getResults();

    /**
     * Pass unhandled methods to the underlying query builder.
     */
    public function __call(string $method, array $parameters)
    {
        $result = $this->query->{$method}(...$parameters);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }
}
