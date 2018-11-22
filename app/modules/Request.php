<?php

/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.18
 */

namespace App\Modules;

class Request
{

    /** @var array */
    private $request;

    /** @var array */
    private $server;

    public function __construct()
    {
        $this->request = $_REQUEST;
        $this->server = filter_input_array(INPUT_SERVER);       
    }

    /**
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return empty($this->request[$name]) ? $default : $this->request[$name];
    }

    /**
     * 
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->server['REQUEST_METHOD'];
    }

    /**
     * 
     * @return string
     */
    public function getRequestedRoute()
    {
        $scriptDir = dirname($this->server['PHP_SELF']);

        $uri = substr($this->server['REQUEST_URI'], strlen($scriptDir));

        $uriData = explode('?', $uri);

        return $uriData[0];
    }

}
