<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sources\Controllers;

use App\Kernel\Controller;

use App\Kernel\Model;
use App\Modules\Database;
use App\Modules\Response;
use App\Modules\Request;
use Sources\Models\Accounts;
use Sources\Models\User;

/**
 * @group user
 */
class UserController extends Controller
{

    /**
     * 
     * @route /profile/:profileId
     *
     *
     * @template views/login.php
     *
     * @method get
     *
     * @type \App\Modules\ResponseUtils::TYPE_HTML
     *
     * @middleware \App\Middlewares\Authentication
     * @middleware \App\Middlewares\Authorization
     *
     * @param Request $request
     * @param Response $response

     * @return Response
     */
    public function profile(Request $request, Response $response)
    {
        return $response->data([
            'name' => $request->get('name', 'Guest'),
            'title' => 'Login Page'
        ]);
    }
}
