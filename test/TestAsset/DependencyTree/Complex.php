<?php

declare(strict_types=1);

namespace LaminasTest\Di\TestAsset\DependencyTree;

class Complex
{
    /** @var Level1 */
    public $result;

    /** @var AdditionalLevel1 */
    public $result2;

    public function __construct(Level1 $dep, AdditionalLevel1 $dep2)
    {
        $this->result  = $dep;
        $this->result2 = $dep2;
    }
}
