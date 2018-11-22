<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 4.4.2016.
 * Time: 01.39
 */

namespace Sources\Models;


use App\Kernel\Model;
use App\Modules\ModelField;
use App\Modules\ModelRelation;

class Accounts extends Model
{

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'accounts';
    }

    /**
     * @return ModelField[]
     */
    public function getModelDefinition()
    {
        return [
            'id' => new ModelField(ModelField::TYPE_INTEGER),
            'user_id' => new ModelRelation(User::class, 'id'),
            'first_name' => new ModelField(ModelField::TYPE_STRING, '', 64),
            'last_name' => new ModelField(ModelField::TYPE_STRING, '', 64),
        ];
    }
}