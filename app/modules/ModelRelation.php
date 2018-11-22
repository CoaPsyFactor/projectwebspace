<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 4.4.2016.
 * Time: 01.45
 */

namespace App\Modules;


use App\Exceptions\ApplicationException;
use App\Kernel\Model;

class ModelRelation extends ModelField
{
    /** @var string */
    private $relationField;

    /** @var Model */
    private $relationModel;

    /**
     * ModelRelation constructor.
     * @param $modelName
     * @param string $relationField
     * @throws ApplicationException
     */
    public function __construct($modelName, $relationField = 'id')
    {
        $this->relationField = (string) $relationField;

        if (false === class_exists($modelName) || false === is_subclass_of($modelName, Model::class)) {
            throw new ApplicationException("{$modelName} is is not Model");
        }

        $reflection = new \ReflectionClass($modelName);

        $this->relationModel = $reflection->newInstance();

        parent::__construct(ModelField::TYPE_RELATION);
    }

    /**
     * @param null $value
     * @return Model|object
     * @throws ApplicationException
     */
    public function getCleanValue($value = null)
    {
        if ($this->relationModel->exists()) {
            return $this->relationModel;
        }

        $cleanValue = $this->relationModel->getModelFieldDefinition($this->relationField)->getCleanValue($value);

        $this->relationModel->fetch([$this->relationField => $cleanValue]);

        return $this->relationModel;
    }

    /**
     * @param null $value
     * @return mixed
     * @throws ApplicationException
     */
    public function getRelationValue($value = null)
    {
        if (false === $this->relationModel instanceof Model) {
            $this->getCleanValue($value);
        }

        return $this->relationModel->getPrimaryKeyFieldDefinition()->getCleanValue($value);
    }
}