<?php
declare(strict_types=1);

namespace Yng\Routing\Annotations;

use Attribute;
use Yng\Routing\Route;

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping extends RequestMapping
{
    protected array $methods = [Route::METHOD_PATCH];
}
