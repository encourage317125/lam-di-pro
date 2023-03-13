<?php

declare(strict_types=1);

namespace LaminasTest\Di\TestAsset\Constructor;

use Countable;
use stdClass;

class UnionTypeConstructorDependency
{
    public function __construct(private stdClass|Countable $someDependency)
    {
    }
}
