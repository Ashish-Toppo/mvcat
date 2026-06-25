<?php

namespace Core\Classes\Relations;

use Core\Classes\Model;

class BelongsTo extends Relation
{
    protected string $foreignKey;
    protected string $ownerKey;

    public function __construct(Model $query, Model $parent, string $foreignKey, string $ownerKey)
    {
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;

        parent::__construct($query, $parent);
    }

    protected function addConstraints(): void
    {
        if ($this->parent->{$this->foreignKey} !== null) {
            $this->query->where($this->ownerKey, '=', $this->parent->{$this->foreignKey});
        }
    }

    public function addEagerConstraints(array $models): void
    {
        $keys = $this->getKeys($models, $this->foreignKey);
        $this->query->whereIn($this->ownerKey, $keys);
    }

    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }
        return $models;
    }

    public function match(array $models, array $results, string $relation): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $dictionary[$result->{$this->ownerKey}] = $result;
        }

        foreach ($models as $model) {
            $key = $model->{$this->foreignKey};
            if (isset($dictionary[$key])) {
                $model->setRelation($relation, $dictionary[$key]);
            }
        }

        return $models;
    }

    public function getResults()
    {
        return $this->query->first();
    }

    protected function getKeys(array $models, string $key): array
    {
        return array_values(array_unique(array_filter(array_map(fn($m) => $m->$key, $models))));
    }
}
