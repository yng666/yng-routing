<?php

declare(strict_types=1);

namespace Yng\Routing;

use Closure;

class Route
{
    /**
     * @var Router
     */
    protected Router $router;

    public const    METHOD_GET      = 'GET';
    public const    METHOD_POST     = 'POST';
    public const    METHOD_HEAD     = 'HEAD';
    public const    METHOD_PUT      = 'PUT';
    public const    METHOD_PATCH    = 'PATCH';
    public const    METHOD_OPTIONS  = 'OPTIONS';
    public const    METHOD_DELETE   = 'DELETE';
    protected const DEFAULT_PATTERN = '[^\/]+';

    /**
     * 请求URI
     *
     * @var string
     */
    protected string $uri;

    /**
     * 请求方法
     *
     * @var array
     */
    protected array $methods;

    /**
     * 目标
     *
     * @var Closure|array|string
     */
    protected $action;

    /**
     * 中间件
     *
     * @var array
     */
    protected array $middlewares = [];

    /**
     * 别名
     *
     * @var ?string
     */
    protected ?string $name = null;

    /**
     * 跨域允许
     *
     * @var array
     */
    protected array $allowCrossDomain = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * 路由参数规则
     *
     * @var array
     */
    protected array $patterns = [];

    /**
     * 路由参数
     *
     * @var array
     */
    protected array $parameters = [];

    /**
     * @var string|null
     */
    protected ?string $regexp = null;

    /**
     * 初始化数据
     * Route constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        foreach ($options as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * 如果设置的uri可编译就编译为正则
     */
    public function compile()
    {
        $regexp = \preg_replace_callback('/[<\[](.+?)[>\]]/', function($matches) {
            [$match, $name] = $matches;
            $hasSlash = '/' === $name[0];
            $newName  = \trim($name, '/');
            $nullable = '[' == $match[0];
            $this->setParameter($newName, null);

            return \sprintf('(?P<%s>%s%s)%s', $hasSlash ? $newName : $name, $hasSlash ? '/' : '', $this->getPattern($newName), $nullable ? '?' : '');
        }, $this->uri);
        if ($regexp !== $this->uri) {
            $this->regexp = \sprintf('#^%s$#iU', $regexp);
        }
    }

    /**
     * @param array $patterns
     *
     * @return $this
     */
    public function patterns(array $patterns): Route
    {
        $this->patterns = array_merge($this->patterns, $patterns);

        return $this;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return $this
     */
    public function setOption(string $name, $value): Route
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * @return string|null
     */
    public function getRegexp(): ?string
    {
        return $this->regexp;
    }

    /**
     * 获取路由参数规则
     *
     * @param string $key
     *
     * @return mixed|string
     */
    public function getPattern(string $key)
    {
        return $this->patterns[$key] ?? static::DEFAULT_PATTERN;
    }

    /**
     * 设置单个路由参数
     *
     * @param string $name
     * @param        $value
     *
     * @return void
     */
    public function setParameter(string $name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * 设置路由参数，全部
     *
     * @param array $parameters
     *
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * 获取单个路由参数
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getParameter(string $name): ?string
    {
        return $this->parameters[$name] ?? null;
    }

    /**
     * 获取全部路由参数
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * 排除某个中间件
     *
     * @param string $middleware
     *
     * @return $this
     */
    public function withoutMiddleware(string $middleware): Route
    {
        if ($key = \array_search($middleware, $this->middlewares)) {
            unset($this->middlewares[$key]);
        }
        return $this;
    }

    /**
     * 设置中间件
     *
     * @param string ...$middlewares
     *
     * @return $this
     */
    public function middleware(string ...$middlewares): Route
    {
        $this->middlewares = [...$this->middlewares, ...$middlewares];

        return $this;
    }

    /**
     * 允许跨域
     *
     * @param string|array $allowDomain
     *
     * @return $this
     */
    public function allowCrossDomain(string ...$allowDomain): Route
    {
        $this->methods[]        = Route::METHOD_OPTIONS;
        $this->allowCrossDomain = $allowDomain;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return array|Closure|string
     */
    public function getAction()
    {
        if (\is_string($this->action) && '' !== $namespace = $this->router->getNamespace()) {
            return \ltrim(\sprintf('%s\\%s', $namespace, $this->action), '\\');
        }
        return $this->action;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return \array_unique(\array_merge($this->router->getMiddlewares(), $this->middlewares));
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name): Route
    {
        Url::set($name, $this);
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAllowCrossDomain(): array
    {
        return $this->allowCrossDomain;
    }
}
