<?php
/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 4.4.2016.
 * Time: 02.58
 */

namespace App\Middlewares;


use App\Kernel\Middleware;
use App\Modules\Application;
use App\Modules\Request;

class Authorization extends Middleware
{

    /**
     * @param Request $request
     * @param Application $application
     * @return mixed
     */
    protected function execute(Request $request, Application $application)
    {
        echo 'authorization<br>';

        $this->next();
    }

    /**
     * @return mixed
     */
    protected function startup()
    {
        // TODO: Implement startup() method.
    }
}