<?php

declare(strict_types=1);

namespace LaminasTest\Di\TestAsset;

class Parameters
{
    /**
     * @param mixed  $a
     * @param string $c
     */
    public function general($a, B $b, $c = 'something')
    {
    }

    public function typehintRequired(A $foo)
    {
    }

    /** @param mixed $bar */
    public function typelessRequired($bar)
    {
    }

    public function typehintOptional(?A $fooOpt = null)
    {
    }

    /** @param bool $flag */
    public function typelessOptional($flag = false)
    {
    }
}
