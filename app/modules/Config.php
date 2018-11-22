<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 28.2.2016.
 * Time: 05.25
 */

namespace App\Modules;


use App\Exceptions\ConfigException;

class Config
{
    /** @var string */
    const CONFIG_PATH = 'src/config.json';

    /** @var array */
    private $_config = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return null|array
     * @throws ConfigException
     */
    public function __call($name, $arguments)
    {
        if (0 !== stripos($name, 'get')) {
            throw new ConfigException("Method {$name} not found");
        }

        $key = strtolower(substr($name, 3));

        if (empty($this->_config[$key])) {
            return null;
        } else if (empty($arguments)) {
            return $this->_config[$key];
        }

        $result = array_intersect_key($this->_config[$key], array_flip($arguments));

        if (1 === count($arguments) && false === empty($result)) {
            return $result[$arguments[0]];
        }

        return $result;
    }

    /**
     * @throws ConfigException
     */
    private function loadConfig()
    {
        $configPath = __DIR__ . '/../../' . self::CONFIG_PATH;

        if (false === file_exists($configPath)) {
            throw new ConfigException("Configuration {$configPath} not found");
        }

        $json = file_get_contents($configPath);

        $this->_config = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new ConfigException("Failed to parse configuration. " . json_last_error_msg());
        }
    }
}