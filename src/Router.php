<?php
declare (strict_types=1);

namespace Yng\Routing;

use Closure;

class Router
{
    /**
     * 分组中间件
     *
     * @var array
     */
    protected array $middlewares = [];

    /**
     * 前缀
     *
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var string
     */
    protected string $namespace = '';

    /**
     * @var array
     */
    protected array $patterns = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
        RouteCollector::$router = $this;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function patch(string $uri, $action): Route
    {
        return $this->request($uri, $action, [Route::METHOD_PATCH]);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function put(string $uri, $action): Route
    {
        return $this->request($uri, $action, [Route::METHOD_PUT]);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function delete(string $uri, $action): Route
    {
        return $this->request($uri, $action, [Route::METHOD_DELETE]);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function post(string $uri, $action): Route
    {
        return $this->request($uri, $action, [Route::METHOD_POST]);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function get(string $uri, $action): Route
    {
        return $this->request($uri, $action, [Route::METHOD_GET, Route::METHOD_HEAD]);
    }

    /**
     * @param string $uri
     * @param        $action
     *
     * @return Route
     */
    public function options(string $uri, $action): Route
    {
        return $this->request($uri, $action, [Route::METHOD_OPTIONS]);
    }

    /**
     * @param string               $uri
     * @param string|Closure|array $action
     * @param array                $methods
     *
     * @return Route
     */
    public function request(string $uri, $action, array $methods = ['GET', 'HEAD', 'POST']): Route
    {
        $route = new Route([
            'router'  => $this,
            'uri'     => '/' . \trim($this->prefix . $uri, '/'),
            'action'  => $action,
            'methods' => $methods,
        ]);
        RouteCollector::add($route);

        return $route;
    }

    /**
     * 分组路由
     *
     * @param Closure $group
     */
    public function group(Closure $group)
    {
        $router                 = RouteCollector::$router;
        RouteCollector::$router = $this;
        $group($this);
        RouteCollector::$router = $router;
    }

    /**
     * 添加中间件
     *
     * @param string ...$middleware
     *
     * @return Router
     */
    public function middleware(string ...$middlewares): Router
    {
        $new              = clone $this;
        $new->middlewares = \array_unique([...$this->middlewares, ...$middlewares]);

        return $new;
    }

    /**
     * @param array|string $pattern
     * @param string|null  $value
     *
     * @return Router
     */
    public function patterns(array $patterns): Router
    {
        $new           = clone $this;
        $new->patterns = \array_merge($this->patterns, $patterns);

        return $new;
    }

    /**
     * 设置前缀
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function prefix(string $prefix): Router
    {
        $new         = clone $this;
        $new->prefix = $this->prefix . $prefix;

        return $new;
    }

    /**
     * @param string $namespace
     *
     * @return Router
     */
    public function namespace(string $namespace): Router
    {
        $new            = clone $this;
        $new->namespace = \sprintf('%s\\%s', $this->namespace, $namespace);

        return $new;
    }
}
