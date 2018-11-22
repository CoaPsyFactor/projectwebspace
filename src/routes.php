<?php

use App\Middlewares\Authentication;

/** @var \App\Modules\Application $app */
$app->group('/group', function ($app) use ($app) {

    $app->get('profile', 'Sources\Controllers\UserController::profile', [], 'views/login.php');

}, [Authentication::class]);
