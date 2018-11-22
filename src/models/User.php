<?php

namespace Sources\Models;

use App\Modules\ModelField;

class User extends DeleteEntity
{

    protected $hidden = ['password'];

    public function getModelDefinition()
    {
        return [
            'id' => new ModelField(ModelField::TYPE_INTEGER),
            'username' => new ModelField(ModelField::TYPE_STRING, null, 64),
            'password' => new ModelField(ModelField::TYPE_STRING, null, 72),
            'status' => new ModelField(ModelField::TYPE_BOOL, false)
        ];
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'users';
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        return 'user';
    }
}