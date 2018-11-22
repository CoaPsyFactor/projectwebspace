<?php

/**
 * Created by PhpStorm.
 * User: zvekete
 * Date: 21.2.2016.
 * Time: 02.17
 */

namespace App\Modules;

use App\Exceptions\ApplicationException;
use App\Kernel\Controller;
use App\Kernel\Di;
use App\Kernel\ExceptionHandler;
use App\Kernel\Middleware;
use Closure;
use Exception;

class Application
{

    /** @var string */
    private $routesPath;

    /** @var Request */
    private $request;

    /** @var Route */
    private $route;

    /** @var array */
    private $routeMiddleware;

    /** @var array */
    private $currentRouteData;

    /** @var Config */
    private $config;

    /**
     * Application constructor.
     *
     * @param Request $request
     * @param Route $route
     * @param Config $config
     */
    public function __construct(Request $request, Route $route, Config $config)
    {
        $this->routesPath = __DIR__ . '/../../src/parsed_routes.json';

        $this->request = $request;
        $this->route = $route;
        $this->config = $config;

        $this->parseControllersRoutes();
    }

    private function parseControllersRoutes()
    {
        $routes = $this->config->getApplication('routes');

        if (empty($routes) || false !== $this->loadRouteFromFile()) {
            return;
        }

        $app = $this;

        foreach ($routes as $route) {
            if (file_exists($route)) {
                require_once __DIR__ . '/../../' . trim($route, '/');
            } else {
                $this->route->registerRoutesInController($route);
            }
        }

        $this->saveRoutesToFile();
    }

    /**
     * @return bool
     */
    public function loadRouteFromFile()
    {
        if (false === is_readable($this->routesPath)) {
            return false;
        }

        $json = file_get_contents($this->routesPath);

        $data = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error() || empty($data[$this->request->getRequestMethod()])) {
            return false;
        }

        foreach (array_values($data[$this->request->getRequestMethod()]) as $route) {

            if (empty($route)) {
                return false;
            }

            foreach ($route as $routePattern => $routeData) {
                $parameters = $this->route->routeMatch($this->request->getRequestedRoute(), $routePattern);

                if (false === $parameters) {
                    continue;
                }


                $this->currentRouteData = $routeData;
                $this->currentRouteData['params'] = $parameters;

                break(2);
            }
        }

        return false === empty($this->currentRouteData);
    }

    public function saveRoutesToFile()
    {
        if (file_exists($this->routesPath) && false === is_writable($this->routesPath)) {
            return;
        }

        file_put_contents($this->routesPath, json_encode($this->route->getRoutes()));
    }

    /**
     * @param $prefix
     * @param Closure $callback
     * @param array $middleware
     */
    public function group($prefix, Closure $callback, array $middleware = [])
    {
        $previousPrefix = $this->route->getPrefix();

        $this->route->setPrefix($prefix);

        $this->routeMiddleware = $middleware;

        $callback($this);

        $this->routeMiddleware = [];

        $this->route->setPrefix($previousPrefix);
    }

    /**
     * @param $route
     * @param $callback
     * @param array $middleware
     * @param null $template
     * @throws \App\Exceptions\RouteException
     */
    public function get($route, $callback, array $middleware = [], $template = null)
    {
        $_middleware = array_keys(array_merge(array_flip($this->routeMiddleware), array_flip($middleware)));

        $this->route->registerRoute('GET', $route, $callback, $_middleware, $template);
    }

    /**
     * @param $route
     * @param $callback
     * @param array $middleware
     * @param null $template
     * @throws \App\Exceptions\RouteException
     */
    public function post($route, $callback, array $middleware = [], $template = null)
    {
        $_middleware = array_keys(array_merge(array_flip($this->routeMiddleware), array_flip($middleware)));

        $this->route->registerRoute('POST', $route, $callback, $_middleware, $template);
    }

    /**
     * @param $route
     * @param $callback
     * @param array $middleware
     * @param null $template
     * @throws \App\Exceptions\RouteException
     */
    public function put($route, $callback, array $middleware = [], $template = null)
    {
        $_middleware = array_keys(array_merge(array_flip($this->routeMiddleware), array_flip($middleware)));

        $this->route->registerRoute('PUT', $route, $callback, $_middleware, $template);
    }

    /**
     * @param $route
     * @param $callback
     * @param array $middleware
     * @param null $template
     * @throws \App\Exceptions\RouteException
     */
    public function delete($route, $callback, array $middleware = [], $template = null)
    {
        $_middleware = array_keys(array_merge(array_flip($this->routeMiddleware), array_flip($middleware)));

        $this->route->registerRoute('DELETE', $route, $callback, $_middleware, $template);
    }

    /**
     *
     */
    public function run()
    {
        if (empty($this->currentRouteData)) {
            $this->currentRouteData = $this->route->getRouteData();
        }

        list($class, $method) = explode('::', $this->currentRouteData['callback']);

        $this->prepareController($this->currentRouteData);

        try {
            ob_start();

            $response = $this->execute($class, $method, $this->currentRouteData['params']);

            $additionalContent = ob_get_clean();
        } catch (Exception $exception) {
            ExceptionHandler::catchException($exception);
        }

        ExceptionHandler::throwExceptions();

        if (isset($response) && $response instanceof Response) {
            $response->additionalBodyContent($additionalContent)->send();
        }
    }

    /**
     * @param array $routeData
     */
    private function prepareController(array $routeData)
    {
        foreach ($routeData['middleware'] as $middleware) {
            Middleware::queue($middleware);
        }

        if (false === empty($routeData['template'])) {
            Di::getInstance(Template::class)->make($routeData['template']);
        }

        if (false === empty($routeData['type'])) {
            Di::getInstance(Response::class)->setContentType($routeData['type']);
        }
    }

    /**
     *
     */
    private function boot()
    {
        try {
            Middleware::boot();
        } catch (Exception $exception) {
            ExceptionHandler::catchException($exception);
        }
    }

    /**
     *
     * @param string $class
     * @param string $method
     * @param array $parameters
     * @return mixed
     *
     * @throws ApplicationException
     */
    private function execute($class, $method, array $parameters = [])
    {
        Middleware::run();

        if (false === Middleware::finished()) {
            return;
        }

        $instance = Di::getInstance($class);

        if (false === $instance instanceof Controller) {
            throw new ApplicationException("{$class} is not controller");
        }

        return Di::autoInvoke($instance, $method, $parameters);
    }

}
