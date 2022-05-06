<?php
declare(strict_types=1);

namespace Yng\Routing\Annotations;

use Yng\Di\Annotations\Annotation;
use Yng\Routing\Contracts\MappingInterface;
use Yng\Routing\Route;
use Yng\Routing\RouteCollector;

#[\Attribute(\Attribute::TARGET_METHOD)]
class RequestMapping extends Annotation implements MappingInterface
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @var null
     */
    protected $allowCrossDomain = null;

    /**
     * @var array
     */
    protected array $patterns = [];

    /**
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @var array|string[]
     */
    protected array $methods = [Route::METHOD_GET, Route::METHOD_HEAD, Route::METHOD_POST];

    /**
     * @param string $controller
     * @param string $method
     *
     * @return void
     */
    public function register(string $controller, string $method)
    {
        $route = RouteCollector::$router->request($this->path, $controller . '@' . $method, $this->methods);
        if ($this->allowCrossDomain) {
            $route->allowCrossDomain(...(array)$this->allowCrossDomain);
        }
        if ($this->name) {
            $route->name($this->name);
        }
        if ($this->patterns) {
            $route->patterns($this->patterns);
        }
        if ($this->middlewares) {
            $route->middleware(...$this->middlewares);
        }
    }
}
