<?php

namespace App\Middlewares;

use App\Kernel\Middleware;
use App\Modules\Application;
use App\Modules\Request;

class Authentication extends Middleware
{
    /**
     * @param Request $request
     * @param Application $application
     * @return mixed
     */
    protected function execute(Request $request, Application $application)
    {
        echo 'authentication<br>';

        $this->next();
    }

    /**
     * @return mixed
     */
    protected function startup()
    {
        
    }
}