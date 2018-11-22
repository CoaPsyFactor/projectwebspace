<?php

namespace App\Modules;

use App\Exceptions\ApplicationException;

class ModelField
{
    const TYPE_INTEGER = 'Int';

    const TYPE_STRING = 'String';

    const TYPE_FLOAT = 'Float';

    const TYPE_BOOL = 'Bool';

    const TYPE_JSON = 'Json';

    const TYPE_ARRAY = 'Array';

    const TYPE_RELATION = 'Relation';

    const TYPE_TEXT = 'Text';

    /** @var string */
    private $type;

    /** @var int */
    private $size;

    /** @var string */
    private $schemaType;

    /** @var mixed */
    private $defaultValue;

    /**
     * @var array
     */
    private static $typeList = [
        self::TYPE_INTEGER => [
            'default_size' => 11,
            'default_value' => 0,
            'schema_type' => 'int'
        ],
        self::TYPE_STRING => [
            'default_size' => 256,
            'default_value' => null,
            'schema_type' => 'varchar'
        ],
        self::TYPE_TEXT => [
            'default_size' => 65535,
            'default_value' => null,
            'schema_type' => 'text'
        ],
        self::TYPE_JSON => [
            'default_size' => 65535,
            'default_value' => '[]',
            'schema_type' => 'text'
        ],
        self::TYPE_ARRAY => [
            'default_size' => 65535,
            'default_value' => '[]',
            'schema_type' => 'text'
        ],
        self::TYPE_BOOL => [
            'default_size' => 1,
            'default_value' => 0,
            'schema_type' => 'tinyint'
        ],
        self::TYPE_FLOAT => [
            'default_size' => 20,
            'default_value' => 0.0,
            'schema_type' => 'float'
        ],
        self::TYPE_RELATION => [
            'default_size' => null,
            'default_value' => null,
            'schema_type' => null
        ]
    ];

    /**
     * ModelField constructor.
     * @param $type
     * @param null $default
     * @param null $size
     * @param null $schemaType
     * @throws ApplicationException
     */
    public function __construct($type, $default = null, $size = null, $schemaType = null)
    {
        if (false === $this->setType($type)) {
            throw new ApplicationException("Invalid ModelField Type {$type}");
        }

        $this->defaultValue = $default;

        if (is_null($default)) {
            $this->defaultValue = self::$typeList[$type]['default_value'];
        }

        $this->size = $size;

        if (is_null($size)) {
            $this->size = self::$typeList[$type]['default_size'];
        }

        $this->schemaType = $schemaType;

        if (is_null($schemaType)) {
            $this->schemaType = self::$typeList[$type]['schema_type'];
        }
    }

    /**
     * @param $value
     * @return mixed
     * @throws ApplicationException
     */
    public function getCleanValue($value = null)
    {
        $method = "get{$this->type}";

        $value = is_null($value) ? $this->defaultValue : $value;

        if (strlen($value) > $this->size) {
            $value = substr($value, 0, $this->size);
        }

        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], $value);
        } else {
            throw new ApplicationException("Invalid ModelField Type {$this->type}");
        }
    }

    /**
     * @param $type
     * @return bool
     */
    public function setType($type)
    {
        if ($this->validType($type)) {
            $this->type = $type;

            return true;
        }

        return false;
    }

    public function setSize($size)
    {
        $this->size = (int) $size;

        return $this;
    }

    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }

    public function getText($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        } else if (is_object($value) && method_exists($value, '__toString') || is_null($value) || is_bool($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * @param $value
     * @return int
     */
    public function getInt($value)
    {
        return (int) $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function getString($value)
    {
        return (string) $value;
    }

    /**
     * @param $value
     * @return float
     */
    public function getFloat($value)
    {
        return (float) $value;
    }

    /**
     * @param $value
     * @return bool
     */
    public function getBool($value)
    {
        return (bool) $value;
    }

    /**
     * @param array $value
     * @return string
     */
    public function getJson(array $value)
    {
        $json = json_decode($value, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            return $json;
        }

        return json_encode([$value]);
    }

    /**
     * @param $value
     *
     * @return array|string
     */
    public function getArray($value)
    {
        $json = json_encode($value);

        if (JSON_ERROR_NONE === json_last_error()) {
            return $json;
        }

        return (array) $value;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function validType($type)
    {
        return isset(self::$typeList[$type]);
    }

}