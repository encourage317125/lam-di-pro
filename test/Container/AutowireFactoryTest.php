<?php

declare(strict_types=1);

namespace LaminasTest\Di\Container;

use Laminas\Di\Container\AutowireFactory;
use Laminas\Di\Exception;
use Laminas\Di\InjectorInterface;
use LaminasTest\Di\TestAsset;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * AutowireFactory test case.
 *
 * @coversDefaultClass Laminas\Di\Container\AutowireFactory
 */
class AutowireFactoryTest extends TestCase
{
    private AutowireFactory $instance;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->instance = new AutowireFactory();
    }

    private function createContainerMock(InjectorInterface $injector): ContainerInterface
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('has')->with(InjectorInterface::class)->willReturn(true);
        $container->method('get')
            ->with(InjectorInterface::class)
            ->willReturn($injector);

        return $container;
    }

    public function testCanCreateUsesInjector()
    {
        $injector = $this->getMockBuilder(InjectorInterface::class)->getMockForAbstractClass();
        $injector->expects($this->atLeastOnce())
            ->method('canCreate')
            ->willReturn(true);

        $container = $this->createContainerMock($injector);
        $result    = $this->instance->canCreate($container, 'AnyClass');

        $this->assertTrue($result);
    }

    private function createContainerForCreateTest(string $className, object $expected): ContainerInterface
    {
        $injector = $this->getMockBuilder(InjectorInterface::class)->getMockForAbstractClass();
        $injector->method('canCreate')
            ->willReturn(true);

        $injector->expects($this->atLeastOnce())
            ->method('create')
            ->with($className)
            ->willReturn($expected);

        return $this->createContainerMock($injector);
    }

    public function testCreateUsesInjector()
    {
        $expected  = new stdClass();
        $className = 'SomeClassName';
        $container = $this->createContainerForCreateTest($className, $expected);
        $result    = $this->instance->create($container, $className);

        $this->assertSame($expected, $result);
    }

    public function testInstanceIsInvokable()
    {
        $expected  = new stdClass();
        $className = 'SomeOtherClassName';
        $container = $this->createContainerForCreateTest($className, $expected);
        $factory   = $this->instance;

        $result = $factory($container, $className);
        $this->assertSame($expected, $result);
    }

    public function testCanCreateReturnsFalseWithoutInjector()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('has')->willReturn(false);

        $this->assertFalse($this->instance->canCreate($container, TestAsset\A::class));
    }

    public function testCreateWithoutInjectorThrowsException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('has')->willReturn(false);

        $this->expectException(Exception\RuntimeException::class);
        $this->instance->create($container, TestAsset\A::class);
    }

    public function testCreateWithInvalidInjectorThrowsException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn(new stdClass());

        $this->expectException(Exception\RuntimeException::class);
        $this->instance->create($container, TestAsset\A::class);
    }
}
