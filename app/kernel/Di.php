<?php

/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.17
 */

namespace App\Kernel;

abstract class Di
{

    /** @var array  */
    private static $instances = [];

    /**
     * @param string $className
     * @param array $parameters
     * @return object instance of $className
     */
    public static function getInstance($className, array $parameters = [])
    {
        if (empty(self::$instances[$className]) || false === self::$instances[$className] instanceof $className) {
            self::loadInstance($className, $parameters);
        }

        return self::$instances[$className];
    }

    /**
     * 
     * @param string $className
     * @param array $parameters
     * @return object
     */
    public static function getNewInstance($className, array $parameters = [])
    {
        return self::loadInstance($className, $parameters, true);
    }

    /**
     * 
     * @param string|object $class
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function autoInvoke($class, $method, array $parameters = [])
    {
        $_method = new \ReflectionMethod($class, $method);

        $dependencies = array_map(function ($value) use (&$parameters) {
            return is_null($value) ? array_shift($parameters) : $value;
        }, self::getDependencies($_method));

        if (is_string($class)) {
            $class = self::getInstance($class);
        }

        return $_method->invokeArgs($class, $dependencies);
    }

    /**
     * Get required parameters for instancing object
     *
     * @param $class
     * @return array
     */
    public static function getDependencies($class, $newInstance = false)
    {
        if (false === $class instanceof \ReflectionMethod) {

            if (false === $class instanceof \ReflectionClass) {
                $class = new \ReflectionClass($class);
            }

            $class = $class->getConstructor();

            if (false === $class instanceof \ReflectionMethod) {
                return [];
            }
        }

        $dependencies = [];

        foreach ($class->getParameters() as $parameter) {
            if ($parameter->getClass() && false === $newInstance) {
                $dependencies[] = Di::getInstance($parameter->getClass()->name);
            } else if ($parameter->getClass() && $newInstance) {
                $dependencies[] = Di::getNewInstance($parameter->getClass()->name);
            } else {
                $dependencies[] = null;
            }
        }

        return $dependencies;
    }

    /**
     * @param string $className
     * @param array $parameters
     * @param bool $newInstance
     * @return object
     */
    private static function loadInstance($className, array $parameters, $newInstance = false)
    {
        $dependencies = $parameters;

        $reflection = new \ReflectionClass($className);

        if (empty($parameters)) {
            $dependencies = self::getDependencies($reflection, $newInstance);
        }

        $instance = $reflection->newInstanceArgs($dependencies);

        if (false === $newInstance) {
            self::$instances[$className] = $instance;
        }

        return $instance;
    }

}
