<?php

declare(strict_types=1);

namespace LaminasTest\Di\TestAsset;

class ClassDefinitionRedundantUaSortTestDependency
{
    /** @param mixed $third */
    public function __construct(
        string $first,
        int $second,
        $third
    ) {
    }
}
