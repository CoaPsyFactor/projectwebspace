<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.57
 */

namespace App\Kernel;

use App\Exceptions\ApplicationException;

abstract class ExceptionHandler
{
    /** @var ApplicationException[] */
    private static $exceptions = [];

    /**
     * @param \Exception $exception
     */
    public static function catchException(\Exception $exception)
    {
        self::$exceptions[] = $exception;
    }

    /**
     * @return bool
     */
    public static function hasExceptions()
    {
        return false === empty(self::$exceptions);
    }

    /**
     * @param \Closure $callback
     * @param array $onFail
     */
    public static function tryCatch(\Closure $callback, array $onFail = [])
    {
        try {
            $callback();
        } catch (\Exception $exception) {
            $exceptionClass = get_class($exception);

            if (empty($onFail[$exceptionClass])) {
                self::catchException($exception);
            } else {
                $onFail[$exceptionClass]($exception);
            }
        }
    }

    /**
     * @throws ApplicationException
     */
    public static function throwExceptions()
    {

        foreach (self::$exceptions as $exception) {
            echo $exception->getMessage();
            echo $exception->getTraceAsString() . '<br>';

            while ($previous = $exception->getPrevious()) {
                echo $exception->getMessage();
                echo $exception->getTraceAsString() . '<br>';

                $exception = $previous;
            }
        }

        if (false === empty(self::$exceptions)) {
            throw new ApplicationException("Application Exception Thrown");
        }
    }
}