<?php

namespace Sources\models;

use App\Kernel\Di;
use App\Kernel\Model;
use App\Modules\Database;

abstract class DeleteEntity extends Model
{
    protected $itemId;

    protected $itemType;

    protected $deleteUser;

    protected $deleteTime;

    /**
     * @return string
     */
    public abstract function getItemType();

    /**
     * @return array
     */
    public function getWithDeleteRecord()
    {
        /** @var Database $db */
        $db = Di::getInstance(Database::class);

        /** @var array $result */
        $result = $db->select(
            $this->_buildQuery(),
            ['itemId' => $this->getPrimaryKeyValue(), 'itemType' => $this->getItemType()],
            true
        );

        var_dump($result);

        return $this->hydrate($result, null, function (Model $model, array $data) {
            $model->setAdditionalAttributes([
                'deleteBy' => empty($data['delete_user']) ? null : $data['delete_user'],
                'deletedAt' => empty($data['delete_time']) ? null : $data['delete_time']
            ]);
        });
    }

    /**
     * @return string
     */
    private function _buildQuery()
    {
        $table = $this->getTableName();

        $query = "
            SELECT
              `{$table}`.*, `deleted_list`.`delete_time`, `deleted_list`.`delete_user`
            FROM `{$table}`
            LEFT JOIN `deleted_list`
            ON
              `deleted_list`.`item_id` = :itemId AND
              `deleted_list`.`item_type` = :itemType
          WHERE `{$table}`.`{$this->primaryKey}` = :itemId;";

        return $query;
	}
}