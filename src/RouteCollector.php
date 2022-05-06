<?php

declare(strict_types=1);

namespace Yng\Routing;

use Exception;
use Yng\Routing\Exceptions\RouteNotFoundException;

class RouteCollector
{
    /**
     * 未分组的全部路由
     *
     * @var array
     */
    protected static array $routes = [];

    /**
     * 编译后的路由
     *
     * @var array
     */
    protected static array $compiled = [];

    /**
     * @var Router
     */
    public static Router $router;

    /**
     * 添加一个路由
     *
     * @param Route $route
     *
     * @return void
     */
    public static function add(Route $route)
    {
        static::$routes[] = $route;
    }

    /**
     * @param string $method
     *
     * @return mixed
     * @throws Exception
     */
    public static function getByMethod(string $method)
    {
        if (isset(static::$compiled[$method])) {
            return static::$compiled[$method];
        }
        throw new Exception('Method not allowed: ' . $method, 405);
    }

    /**
     * 直接替换路由
     *
     * @param array $routes
     *
     * @return void
     */
    public static function replace(array $routes)
    {
        static::$routes = $routes;
    }

    /**
     * 全部
     *
     * @return array
     */
    public static function all(): array
    {
        return static::$compiled;
    }

    /**
     * @return void
     */
    public function flush()
    {
        static::$routes = [];
    }

    /**
     * 编译路由
     */
    public static function compile()
    {
        /** @var Route $route */
        foreach (static::$routes as $key => $route) {
            $route->compile();
            foreach ($route->getMethods() as $method) {
                static::$compiled[$method][] = $route;
            }
            unset(static::$routes[$key]);
        }
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @return Route
     * @throws RouteNotFoundException
     */
    public static function resolve(string $method, string $uri): Route
    {
        /* @var Route $route */
        foreach (static::getByMethod($method) as $route) {
            // 相等匹配
            if ($route->getUri() === $uri) {
                return $route;
            }
            // 正则匹配
            $regexp = $route->getRegexp();
            if (!\is_null($regexp) && \preg_match($regexp, $uri, $match)) {
                if (!empty($match)) {
                    foreach ($route->getParameters() as $key => $value) {
                        if (\array_key_exists($key, $match)) {
                            $route->setParameter($key, trim($match[$key], '/'));
                        }
                    }
                }
                return $route;
            }
        }
        throw new RouteNotFoundException('Not Found', 404);
    }

    /**
     * 刷新
     *
     * @return void
     */
    public static function refresh()
    {
        static::$router = new Router();
    }
}
