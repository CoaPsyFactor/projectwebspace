<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

use App\Kernel\Di;
use App\Modules\Application;

spl_autoload_register(function ($class) {
    $data = explode('\\', $class);

    if (0 === strcasecmp($data[0], 'sources')) {
        $data[0] = 'src';
    }

    $className = array_pop($data);

    $path = strtolower(implode('/', $data));
    
    require_once __DIR__ . "/{$path}/{$className}.php";
});

Di::getInstance(Application::class)->run();