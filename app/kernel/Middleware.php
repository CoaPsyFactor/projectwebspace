<?php

/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.19
 */

namespace App\Kernel;

use App\Modules\Application;
use App\Modules\Request;

abstract class Middleware
{

    /**
     * @param Request $request
     * @param Application $application
     * @return mixed
     */
    protected abstract function execute(Request $request, Application $application);

    /**
     * @return mixed
     */
    protected abstract function startup();

    /** @var Middleware[]  */
    private static $queue = [];

    /** @var int */
    private static $queued = 0;

    /**
     * @param Middleware $middleware
     */
    public static function queue($middleware)
    {
        if (false === $middleware instanceof Middleware) {
            $middleware = (new \ReflectionClass($middleware))->newInstance();
        }

        self::$queue[] = $middleware;

        self::$queued = count(self::$queue);
    }

    /**
     *
     */
    public static function run()
    {
        /** @var Application $application */
        $application = Di::getInstance(Application::class);

        /** @var Request $request */
        $request = Di::getInstance(Request::class);

        /** @var Middleware $middleware */
        $middleware = current(self::$queue);

        if ($middleware instanceof Middleware) {
            $middleware->execute($request, $application);
        }
    }

    /**
     * Calls next middleware, if not called controller wont be loaded
     */
    public static function next()
    {
        next(self::$queue);
        
        self::$queued--;

        self::run();
    }

    public static function boot()
    {
        foreach (self::$queue as $middleware) {
            $middleware->startup();
        }
    }

    /**
     * @return Middleware[]
     */
    public static function getQueue()
    {
        return self::$queue;
    }

    /**
     * 
     * @return bool
     */
    public static function finished()
    {
        return 0 === (int) self::$queued;
    }

}
