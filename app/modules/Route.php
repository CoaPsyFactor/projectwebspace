<?php

/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.18
 */

namespace App\Modules;

use App\Exceptions\ApplicationException;
use App\Exceptions\RouteException;
use App\Kernel\Di;
use Closure;

class Route
{

    /** @var array */
    private $routes = ['GET' => [], 'POST' => [], 'PUT' => [], 'DELETE' => []];

    /** @var string */
    private $prefix = '/';

    /** @var Request */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * 
     * @return array
     * @throws RouteException
     */
    public function getRouteData()
    {
        $requestRoute = $this->request->getRequestedRoute();

        list($routePattern, $callback, $middleware, $parameters, $template, $type) = $this->matchRoute($requestRoute);

        if (empty($routePattern)) {
            throw new RouteException("Route {$requestRoute} not found");
        } else if (empty($callback)) {
            throw new RouteException("Route[{$requestRoute}] is missing controller");
        }

        return ['routePattern' => $routePattern, 'callback' => $callback, 'params' => $parameters, 'middleware' => $middleware, 'template' => $template, 'type' => $type];
    }

    /**
     * @param $route
     * @param $routePattern
     * @return array|bool
     */
    public function routeMatch($route, $routePattern)
    {
        $route = trim($route, '/');

        if (false == preg_match_all('/^' . $routePattern . '$/', $route, $matches)) {
            return false;
        }

        unset($matches[0]);

        $parameters = [];

        if (is_array($matches)) {
            $parameters = array_map(function($value) {
                return $value[0];
            }, $matches);
        }

        return $parameters;
    }

    /**
     * 
     * @param array $classes
     */
    public function registerRoutesInControllers(array $classes)
    {
        foreach ($classes as $class) {
            $this->registerRoutesInController($class);
        }
    }

    /**
     * 
     * @param string $class
     * @throws RouteException
     */
    public function registerRoutesInController($class)
    {
        $reflection = new \ReflectionClass($class);

        /* @var $annotationParser AnnotationParser */
        $annotationParser = Di::getInstance(AnnotationParser::class);

        $groups = $annotationParser->getClassAnnotation($class, 'group');

        $group = empty($groups['group'][0]) ? '/' : $groups['group'][0];

        $previousPrefix = $this->prefix;

        $this->setPrefix($group);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->registerMethodRoute($class, $method);
        }

        $this->setPrefix($previousPrefix);
    }

    /**
     * 
     * @param string $classMethod
     * @param string $route
     * @param array $middleware
     * @param array $methods
     * @param string $template
     * @param string $type
     *
     */
    public function registerControllerRoute($classMethod, $route, array $middleware, array $methods, $template = null, $type = null)
    {
        foreach ($methods as $method) {
            $this->registerRoute(strtoupper($method), $route, $classMethod, $middleware, $template, $type);
        }
    }

    /**
     * 
     * @param string $prefix
     */
    public function setPrefix($prefix = '/')
    {
        $this->prefix = trim($prefix, '/') . '/';
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * 
     * @param string $method
     * @param string $route
     * @param string|Closure $callback
     * @param array $middleware
     * @param string $template
     * @param string $type
     * @throws ApplicationException
     */
    public function registerRoute($method, $route, $callback, array $middleware = [], $template = null, $type = ResponseUtils::TYPE_HTML)
    {
        $_method = strtoupper($method);

        if (false === isset($this->routes[$_method])) {
            throw new RouteException("Unknown method {$_method}");
        }

        $_route = trim($route, '/');
        
        $cleanRoute = $this->clearRoute("{$this->prefix}{$_route}");

        $this->routes[$_method][$cleanRoute['count']][$cleanRoute['route']] = [
            'callback' => $callback,
            'middleware' => $middleware,
            'template' => $template,
            'type' => $type,
        ];
    }

    /**
     * @param $requestRoute
     * @return array
     */
    private function matchRoute($requestRoute)
    {
        $requestRoutes = $this->routes[$this->request->getRequestMethod()];

        $parameters = [];
        $routePattern = $data = null;

        ksort($requestRoutes);

        foreach ($requestRoutes as $paramNum => $routes) {
            $routePattern = $data = null;

            foreach ($routes as $routePattern => $data) {
                $parameters = $this->routeMatch($requestRoute, $routePattern);

                if (false !== $parameters) {
                    break(2);
                }
            }
        }

        if (false === $parameters) {
            return [null, null, null, null, null, null, null];
        }



        return [$routePattern, $data['callback'], $data['middleware'], $parameters, $data['template'], $data['type']];
    }

    /**
     * @param string $class
     * @param \ReflectionMethod $method
     * @throws RouteException
     */
    private function registerMethodRoute($class, \ReflectionMethod $method)
    {
        $classMethod = "{$class}::{$method->getName()}";

        /* @var $annotationParser AnnotationParser */
        $annotationParser = Di::getInstance(AnnotationParser::class);

        $parameters = $annotationParser->getMethodAnnotation($classMethod);

        if (empty($parameters['route'][0])) {
            return;
        }

        if (empty($parameters['method'])) {
            throw new RouteException("Controller {$classMethod} missing route method");
        }

        $this->registerControllerRoute(
            $classMethod,
            $parameters['route'][0],
            empty($parameters['middleware']) ? [] : $parameters['middleware'],
            empty($parameters['method']) ? [] : $parameters['method'],
            empty($parameters['template'][0]) ? null : $parameters['template'][0],
            empty($parameters['type'][0]) ? ResponseUtils::TYPE_HTML : constant($parameters['type'][0])
        );
    }

    /**
     * @param $route
     * @return string
     */
    private function clearRoute($route)
    {
        $count = 0;

        $routeData = explode('/', trim($route, '/'));

        foreach ($routeData as $idx => $routePart) {
            $routeData[$idx] = preg_replace('/:([a-zA-Z]+)/', '([a-zA-Z0-9\s_-]+)', $routePart, -1, $c);

            $count += $c;
        }

        return ['route' => implode('\/', $routeData), 'count' => $count];
    }

}
