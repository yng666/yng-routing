<?php
declare(strict_types=1);

namespace Yng\Routing;

use Exception;

class Url
{
    /**
     * 别名
     *
     * @var array
     */
    protected static array $names = [];

    /**
     * 使用url获取路由别名
     *
     * @param $url
     *
     * @return false|int|string
     */
    public static function getAliasByUri($url)
    {
        if (false !== ($key = array_search($url, static::$names))) {
            return $key;
        }
        return null;
    }

    public static function set(string $name, Route $route)
    {
        // TODO 重复alias
        static::$names[$name] = $route;
    }

    /**
     * 获取路由别名
     *
     * @param string $name
     * @param array  $args 关联数组
     *
     * @return string
     * @throws Exception
     */
    public static function build(string $name, array $args = []): string
    {
        if (isset(static::$names[$name])) {
            return \preg_replace_callback([
                '/<(\w+)>/',
                '/\[\/?(\w+)\]/'
            ], function($matches) use (&$args) {
                $key = $matches[1];
                if (isset($args[$key])) {
                    $value = $args[$key];
                    unset($args[$key]);
                    return $value;
                }
                return '';
            }, static::$names[$name]->getUri()); // TODO参数正则限制
        }

        return $name . '?' . \http_build_query($args);
    }
}
