<?php

namespace Core\Classes;

use PDO;
use PDOException;
use Exception;
use ArrayAccess;
use JsonSerializable;
use Core\Classes\Relations\HasMany;
use Core\Classes\Relations\BelongsTo;
use Core\Classes\Relations\Relation;

abstract class Model implements ArrayAccess, JsonSerializable
{
    public static string $table = '';
    private static ?PDO $db = null;

    /** Active Record State **/
    protected array $attributes = [];
    protected array $original = [];
    protected array $relations = [];

    /** Configuration **/
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected array $with = [];

    /** Query Builder State **/
    protected array $whereClause = [];
    protected array $joins = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $eagerLoad = []; 

    public function __construct(array $attributes = [])
    {
        $this->syncOriginal();
        $this->fill($attributes);
    }

    /** Database Connection **/
    public static function db(): PDO
    {
        if (!self::$db) {
            $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8mb4';
            try {
                self::$db = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("DB connection failed: " . $e->getMessage());
            }
        }
        return self::$db;
    }

    public function query(string $sql, array $params = [])
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function runQuery(string $sql, array $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function beginTransaction(): bool { return self::db()->beginTransaction(); }
    public function commit(): bool { return self::db()->commit(); }
    public function rollBack(): bool { return self::db()->rollBack(); }

    public static function query(): self
    {
        return new static();
    }

    /** Active Record & Attributes **/
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } else if (empty($this->fillable)) {
                // If fillable is empty, act unguarded
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    protected function isFillable(string $key): bool
    {
        return in_array($key, $this->fillable);
    }

    public function setAttribute(string $key, $value)
    {
        $this->attributes[$key] = $this->castAttribute($key, $value);
    }

    public function getAttribute(string $key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        
        if (method_exists($this, $key)) {
            return $this->getRelationValue($key);
        }

        return null;
    }

    protected function castAttribute(string $key, $value)
    {
        if (is_null($value)) return null;

        $castType = $this->casts[$key] ?? null;

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }

    public function syncOriginal(): self
    {
        $this->original = $this->attributes;
        return $this;
    }

    public function getDirty(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    public function isDirty(string $attribute = null): bool
    {
        $dirty = $this->getDirty();
        if ($attribute) {
            return array_key_exists($attribute, $dirty);
        }
        return count($dirty) > 0;
    }

    public function save(): bool
    {
        if (isset($this->attributes['id']) && !empty($this->original)) {
            $dirty = $this->getDirty();
            if (empty($dirty)) return true;

            $setClauses = [];
            $params = ['id' => $this->attributes['id']];
            foreach ($dirty as $column => $value) {
                if (is_array($value)) $value = json_encode($value);
                
                $setClauses[] = "$column = :$column";
                $params[$column] = $value;
            }

            $sql = "UPDATE " . static::$table . " SET " . implode(', ', $setClauses) . " WHERE id = :id";
            $stmt = self::db()->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) $this->syncOriginal();
            return $result;
        } else {
            $insertData = [];
            foreach ($this->attributes as $column => $value) {
                $insertData[$column] = is_array($value) ? json_encode($value) : $value;
            }
            
            $columns = array_keys($insertData);
            $placeholders = array_map(fn($col) => ':' . $col, $columns);

            $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = self::db()->prepare($sql);
            
            if ($stmt->execute($insertData)) {
                $this->attributes['id'] = (int) self::db()->lastInsertId();
                $this->syncOriginal();
                return true;
            }
            return false;
        }
    }

    public static function insert(array $data): int|string|false
    {
        if (empty($data)) throw new Exception("Insert data cannot be empty.");
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);
        $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = self::db()->prepare($sql);
        if ($stmt->execute($data)) {
            return self::db()->lastInsertId();
        }
        return false;
    }

    public function update(mixed $idOrData, array $data = []): bool
    {
        if (is_array($idOrData)) {
            // Instance update: $user->update(['name' => 'John']);
            $this->fill($idOrData);
            return $this->save();
        }

        // Static update: Model::update(1, ['name' => 'John'])
        if (empty($data)) throw new Exception("Update data cannot be empty.");
        $setClauses = [];
        $params = ['id' => $idOrData];
        foreach ($data as $column => $value) {
            $setClauses[] = "$column = :$column";
            $params[$column] = is_array($value) ? json_encode($value) : $value;
        }
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($params);
    }

    /** Query Builder **/
    public function where(string $column, string $operator, $value, string $boolean = 'AND'): self
    {
        $allowedOperators = ['=', '<', '>', '<=', '>=', '<>', '!=', 'LIKE'];
        if (!in_array(strtoupper($operator), $allowedOperators, true)) {
            throw new Exception("Invalid operator '$operator' in where clause");
        }
        $this->whereClause[] = ['boolean' => $boolean, 'type' => 'Basic', 'column' => $column, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        if (empty($values)) {
            return $this->where('1', '=', '0', $boolean); // Impossible condition
        }
        $this->whereClause[] = ['boolean' => $boolean, 'type' => 'In', 'column' => $column, 'values' => array_values($values)];
        return $this;
    }

    public function andWhere(string $column, string $operator, $value): self
    {
        return $this->where($column, $operator, $value, 'AND');
    }

    public function orWhere(string $column, string $operator, $value): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = compact('column', 'direction');
        return $this;
    }

    public function limit(int $limit, ?int $offset = null): self
    {
        $this->limit = $limit;
        if ($offset !== null) $this->offset = $offset;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    protected function compileWhere(array &$params): string
    {
        if (empty($this->whereClause)) return '';

        $conditions = [];
        foreach ($this->whereClause as $index => $where) {
            $bool = $index === 0 ? '' : $where['boolean'] . ' ';
            $col = $where['column'];
            
            if ($where['type'] === 'Basic') {
                $paramKey = "val$index";
                $conditions[] = "{$bool}{$col} {$where['operator']} :{$paramKey}";
                $params[$paramKey] = $where['value'];
            } elseif ($where['type'] === 'In') {
                $inKeys = [];
                foreach ($where['values'] as $i => $val) {
                    $paramKey = "val_{$index}_{$i}";
                    $inKeys[] = ":$paramKey";
                    $params[$paramKey] = $val;
                }
                $conditions[] = "{$bool}{$col} IN (" . implode(', ', $inKeys) . ")";
            }
        }
        return " WHERE " . implode(" ", $conditions);
    }

    protected function compileJoins(): string
    {
        if (empty($this->joins)) return '';
        $sql = [];
        foreach ($this->joins as $join) {
            $sql[] = "{$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        return " " . implode(" ", $sql);
    }
    
    public function count(): int
    {
        $params = [];
        $sql = "SELECT COUNT(*) FROM " . static::$table;
        $sql .= $this->compileJoins();
        $sql .= $this->compileWhere($params);
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function get(): array
    {
        $params = [];
        $sql = "SELECT * FROM " . static::$table;
        $sql .= $this->compileJoins();
        $sql .= $this->compileWhere($params);

        if (!empty($this->orderBy)) {
            $orders = [];
            foreach ($this->orderBy as $order) {
                $dir = strtoupper($order['direction']) === 'DESC' ? 'DESC' : 'ASC';
                $orders[] = "{$order['column']} {$dir}";
            }
            $sql .= " ORDER BY " . implode(", ", $orders);
        }

        if ($this->limit !== null) $sql .= " LIMIT " . (int)$this->limit;
        if ($this->offset !== null) $sql .= " OFFSET " . (int)$this->offset;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        $models = [];
        foreach ($results as $result) {
            $model = new static();
            // Raw assignment bypasses fillable restrictions when hydrating from DB
            foreach($result as $k => $v) {
                $model->attributes[$k] = $model->castAttribute($k, $v);
            }
            $model->syncOriginal();
            $models[] = $model;
        }

        if (count($this->eagerLoad) > 0) {
            $models = $this->eagerLoadRelations($models);
        }

        return $models;
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return count($results) > 0 ? $results[0] : null;
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find($id)
    {
        return static::query()->where('id', '=', $id)->first();
    }

    /** Relationships & Eager Loading **/
    public static function with($relations): self
    {
        if (is_string($relations)) $relations = func_get_args();
        $instance = static::query();
        $instance->eagerLoad = $relations;
        return $instance;
    }

    public function eagerLoadRelations(array $models): array
    {
        foreach ($this->eagerLoad as $relationName) {
            if (empty($models)) continue;
            
            $relation = $models[0]->{$relationName}();
            
            if (!$relation instanceof Relation) {
                throw new Exception("Method {$relationName} must return a Relation object.");
            }
            
            $relation->addEagerConstraints($models);
            $models = $relation->initRelation($models, $relationName);
            $results = $relation->getResults();
            $models = $relation->match($models, $results, $relationName);
        }
        return $models;
    }

    public function hasMany(string $related, string $foreignKey = null, string $localKey = 'id'): HasMany
    {
        $foreignKey = $foreignKey ?: strtolower(basename(str_replace('\\', '/', static::class))) . '_id';
        $instance = new $related;
        return new HasMany($instance->query(), $this, $foreignKey, $localKey);
    }

    public function belongsTo(string $related, string $foreignKey = null, string $ownerKey = 'id'): BelongsTo
    {
        $foreignKey = $foreignKey ?: strtolower(basename(str_replace('\\', '/', $related))) . '_id';
        $instance = new $related;
        return new BelongsTo($instance->query(), $this, $foreignKey, $ownerKey);
    }

    public function setRelation($relation, $value)
    {
        $this->relations[$relation] = $value;
        return $this;
    }

    public function getRelationValue(string $key)
    {
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        $relation = $this->$key();
        if (!$relation instanceof Relation) {
            throw new Exception("Relationship method must return a Relation object.");
        }

        $results = $relation->getResults();
        $this->setRelation($key, $results);

        return $results;
    }

    /** ArrayAccess & JsonSerializable **/
    public function offsetExists(mixed $offset): bool { return !is_null($this->getAttribute($offset)); }
    public function offsetGet(mixed $offset): mixed { return $this->getAttribute($offset); }
    public function offsetSet(mixed $offset, mixed $value): void { $this->setAttribute($offset, $value); }
    public function offsetUnset(mixed $offset): void { unset($this->attributes[$offset]); }

    public function toArray(): array
    {
        $data = array_merge($this->attributes, $this->relations);
        foreach ($this->hidden as $hide) {
            unset($data[$hide]);
        }
        return $data;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /** Magic Methods **/
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public static function __callStatic($method, $parameters)
    {
        return static::query()->$method(...$parameters);
    }
}
