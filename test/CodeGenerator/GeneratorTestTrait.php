<?php

declare(strict_types=1);

namespace LaminasTest\Di\CodeGenerator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

trait GeneratorTestTrait
{
    /** @var vfsStreamDirectory */
    private $root;

    /** @var string */
    private $dir;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup('laminas-di');
        $this->dir  = $this->root->url();
    }
}
