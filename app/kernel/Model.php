<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.19
 */

namespace App\Kernel;

use App\Exceptions\ModelException;
use App\Modules\Database;
use App\Modules\ModelField;
use App\Modules\ModelRelation;

abstract class Model
{
    /** @var Database */
    private $db;

    /** @var array */
    private $originalData = [];

    /** @var array */
    private $updateData = [];

    /** @var string */
    protected $primaryKey = 'id';

    /** @var array */
    protected $hidden = [];

    /** @var bool */
    private $found = false;

    /** @var array */
    private $additional = [];

    /** @var bool */
    private $_getRelation = false;

    /** @var ModelField[][] */
    private static $modelFields = [];

    /** @var array  */
    private static $reservedKeywords = [
        'db' => true, 'originaldata' => true, 'updatedata' => true, 'primarykey' => true, 'hidden' => true, 'found' => true, 'modelinfo' => true
    ];

    /**
     * @return string
     */
    public abstract function getTableName();

    /**
     * @return ModelField[]
     */
    public abstract function getModelDefinition();

    /**
     * Model constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (empty(self::$modelFields[static::class])) {
            self::$modelFields[static::class] = [true];

            self::$modelFields[static::class] = $this->validateModelFields($this->getModelDefinition());
        }

        $this->hydrate($data);

        $this->db = Di::getInstance(Database::class);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setAdditionalAttribute($name, $value)
    {
        $this->additional[$name] = $value;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setAdditionalAttributes(array $data)
    {
        foreach ($data as $name => $value) {
            $this->setAdditionalAttribute($name, $value);
        }

        return $this;
    }

    private function validateModelFields(array $fields)
    {
        foreach ($fields as $field => $modelField) {
            if (false === $modelField instanceof ModelField) {
                throw new ModelException("Field {$field} is not ModelField");
            }
        }

        return $fields;
    }

    /**
     * @return mixed
     */
    public function getPrimaryKeyValue()
    {
        return $this->getAttribute($this->primaryKey, null);
    }

    /**
     * @return ModelField|null
     */
    public function getPrimaryKeyFieldDefinition()
    {
        return $this->getModelFieldDefinition($this->primaryKey);
    }


    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (false === empty($this->additional[$name]) && empty($this->updateData[$name])) {
            return $this->additional[$name];
        } else if (empty($this->updateData[$name])) {
            return $default;
        }

        return $this->updateData[$name];
    }

    /**
     * @param array $names
     * @return array
     */
    public function getAttributes(array $names = [])
    {
        return array_intersect_key(array_flip($names), $this->updateData);
    }

    /**
     * @return array
     */
    public function getAllAttributes()
    {
        return array_diff_key($this->updateData, array_flip($this->hidden));
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->getAllAttributes());
    }

    /**
     * @param $name
     * @param $value
     * @throws ModelException
     */
    public function setAttribute($name, $value)
    {
        if (empty(self::$modelFields[static::class][$name])) {
            throw new ModelException("Field {$name} does not exist");
        }

        if (false === self::$modelFields[static::class][$name] instanceof ModelRelation) {
            $this->updateData[$name] = self::$modelFields[static::class][$name]->getCleanValue($value);
        } else {
            $this->updateData[$name] = self::$modelFields[static::class][$name]->getRelationValue($value);
        }
    }

    /**
     * @param \Closure|null $callback
     */
    public function save(\Closure $callback = null)
    {

    }

    /**
     * @param string $relation
     * @return Model
     * @throws ModelException
     */
    public function getRelation($relation)
    {
        /** @var ModelRelation $modelField */
        $modelField = $this->getModelFieldDefinition($relation);

        if (false === $modelField instanceof ModelRelation) {
            throw new ModelException("{$relation} is not relation");
        }

        $this->_getRelation = true;

        return call_user_func([$this, '__get'], $relation);
    }

    /**
     * @param array $criteria
     * @param array $fields
     * @param callable|null $beforeHydrate
     * @param callable|null $afterHydrate
     * @return Model
     */
    public function fetch(array $criteria = [], array $fields = [], callable $beforeHydrate = null, callable $afterHydrate = null)
    {
        $this->found = false;

        $query = "SELECT {$this->parseFields($fields)} FROM {$this->getTableName()}";
        $bindings = [];

        if (false === empty($criteria)) {
            list($criteriaString, $bindings) = $this->buildCriteria($criteria);

            $query .= " WHERE {$criteriaString}";
        }

        $query .= ' LIMIT 1';

        $data = $this->db->select($query, $bindings, true);

        $this->found = false === empty($data);

        return $this->hydrate($data, $beforeHydrate, $afterHydrate);
    }

    /**
     * @return Model
     */
    public function reset()
    {
        $this->updateData = [];

        return $this->hydrate($this->originalData);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->found;
    }

    /**
     * @param array $data
     * @param callable|null $before
     * @param callable|null $after
     * @return $this
     * @throws ModelException
     */
    public function hydrate(array $data, callable $before = null, callable $after = null)
    {
        $this->clear();

        $beforeData = is_callable($before) ? $before($data) : true;

        if (false === $beforeData) {
            return $this;
        }

        $this->originalData = is_array($beforeData) ? $beforeData : $data;

        foreach ($data as $field => $value) {
            if (empty(self::$modelFields[static::class][$field])) {
                continue;
            } else if (self::$modelFields[static::class][$field] instanceof ModelRelation) {
                $this->updateData[$field] = null;
            } else {
                $this->setAttribute($field, $value);
            }
        }

        false === is_callable($after) ?: $after($this, $data);

        return $this;
    }

    /**
     * @param $field
     * @return ModelField|null
     */
    public function getModelFieldDefinition($field)
    {
        if (empty(self::$modelFields[static::class][$field])) {
            return null;
        }

        return self::$modelFields[static::class][$field];
    }

    /**
     * @param $name
     * @param $value
     * @throws ModelException
     */
    public function __set($name, $value)
    {
        if (isset(self::$reservedKeywords[$name])) {
            throw new ModelException("{$name} is reserved keyword");
        }

        $this->setAttribute($name, $value);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \App\Exceptions\ApplicationException
     */
    public function __get($name)
    {
        if (
            empty($this->updateData[$name]) &&
            false === empty(self::$modelFields[static::class][$name]) &&
            self::$modelFields[static::class][$name] instanceof ModelRelation
        ) {
            $this->updateData[$name] = self::$modelFields[static::class][$name]->getRelationValue($this->getPrimaryKeyValue());

            if ($this->_getRelation) {
                $this->_getRelation = false;

                return self::$modelFields[static::class][$name]->getCleanValue($this->getPrimaryKeyValue());
            }
        }

        return $this->getAttribute($name);
    }

    /**
     * @param array $criteria
     * @return array
     */
    private function buildCriteria(array $criteria)
    {
        $criteriaArray = [];
        $values = [];

        foreach ($criteria as $field => $value) {
            $modelField = $this->getModelFieldDefinition($field);

            if (is_null($modelField)) {
                continue;
            }

            $criteriaArray[] = "{$field} = :{$field}";

            if ($modelField instanceof ModelRelation) {
                $values[":{$field}"] = $modelField->getRelationValue($value);
            } else {
                $values[":{$field}"] = $modelField->getCleanValue($value);
            }
        }

        return [implode(' AND ', $criteriaArray), $values];
    }

    /**
     * @param array $fields
     * @return string
     */
    private function parseFields(array $fields)
    {
        if (empty($fields)) {
            return '*';
        } else {
            return '`' . implode('`,`', $fields) . '`';
        }
    }

    /**
     * Unsets all data
     */
    private function clear()
    {
        $this->found = false;

        foreach ($this->updateData as $field => $value) {
            unset($this->updateData[$field]);
        }

        foreach ($this->originalData as $field => $value) {
            unset($this->originalData[$field]);
        }
    }
}