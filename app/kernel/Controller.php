<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Kernel;

/**
 * Description of Controller
 *
 * @author Zvekete
 */
abstract class Controller
{
    
    /**
     * 
     * e.g
     *  UserController::methodShowProfile() will return \Namespace\To\UserController::showProfile
     * 
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (0 === strpos($name, 'method')) {
            $method = lcfirst(substr($name, 6));
            
            return static::class . '::' . $method;
        }
    }
}
