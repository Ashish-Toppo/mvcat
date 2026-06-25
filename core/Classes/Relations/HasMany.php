<?php

namespace Core\Classes\Relations;

use Core\Classes\Model;

class HasMany extends Relation
{
    protected string $foreignKey;
    protected string $localKey;

    public function __construct(Model $query, Model $parent, string $foreignKey, string $localKey)
    {
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;

        parent::__construct($query, $parent);
    }

    protected function addConstraints(): void
    {
        // Don't add constraint if we are eager loading (no parent ID available)
        if ($this->parent->{$this->localKey} !== null) {
            $this->query->where($this->foreignKey, '=', $this->parent->{$this->localKey});
        }
    }

    public function addEagerConstraints(array $models): void
    {
        $keys = $this->getKeys($models, $this->localKey);
        $this->query->whereIn($this->foreignKey, $keys);
    }

    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, []);
        }
        return $models;
    }

    public function match(array $models, array $results, string $relation): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->foreignKey}][] = $result;
        }

        foreach ($models as $model) {
            $key = $model->{$this->localKey};
            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    public function getResults()
    {
        return $this->query->get();
    }

    protected function getKeys(array $models, string $key): array
    {
        return array_values(array_unique(array_filter(array_map(fn($m) => $m->$key, $models))));
    }
}
