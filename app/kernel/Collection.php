<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 23.2.2016.
 * Time: 03.08
 */

namespace App\Kernel;

abstract class Collection
{
    /** @var Model[] */
    private $collections = [];

    /**
     * @param Model $model
     */
    public function add(Model $model) : Collection
    {
        $primaryKey = $model->getPrimaryKeyValue();
        $modelClass = get_class($model);

        $this->collections[$modelClass][$primaryKey] = $model;
    }

    /**
     * @param string $class
     * @param array $criteria
     * @param string $primaryKey
     * @return Model|null
     */
    public function get($class, array $criteria, $primaryKey = 'id')
    {
        if (empty($this->collections[$class])) {
            return null;
        }

        $exists = false == (empty($criteria[$primaryKey]) && empty($this->collections[$class][$criteria[$primaryKey]]));

        if ($exists) {
            return $this->collections[$class][$criteria[$primaryKey]];
        }

        /** @var Model $model */
        foreach ($this->collections[$class] as $model) {
            $isCorrect = array_filter($criteria, function ($property, $value) use ($model) {
                return $model->getAttribute($property, null) == $value ? true : null;
            }, ARRAY_FILTER_USE_BOTH);

            if (false === empty($isCorrect)) {
                return $model;
            }
        }

        return null;
    }

}