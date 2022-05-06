<?php
declare(strict_types=1);

namespace Yng\Routing\Contracts;

interface MappingInterface
{
    public function register(string $controller, string $method);
}
