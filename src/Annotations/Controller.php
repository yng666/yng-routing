<?php
declare(strict_types=1);

namespace Yng\Routing\Annotations;

use Attribute;
use Yng\Di\Annotations\Annotation;
use Yng\Routing\RouteCollector;
use Yng\Routing\Router;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller extends Annotation
{
    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @param ...$args
     */
    public function __construct(...$args)
    {
        parent::__construct($args);
        RouteCollector::$router = new Router([
            'prefix'      => $this->prefix,
            'middlewares' => $this->middlewares,
        ]);
    }

    /**
     * 刷新Router
     */
    public function __destruct()
    {
        RouteCollector::refresh();
    }
}
