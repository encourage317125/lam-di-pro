<?php

declare(strict_types=1);

namespace LaminasTest\Di\Container;

use Laminas\Di\CodeGenerator\InjectorGenerator;
use Laminas\Di\Config;
use Laminas\Di\ConfigInterface;
use Laminas\Di\Container\GeneratorFactory;
use Laminas\Di\Injector;
use Laminas\ServiceManager\ServiceManager;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionClass;

use function uniqid;

/**
 * @covers Laminas\Di\Container\GeneratorFactory
 */
class GeneratorFactoryTest extends TestCase
{
    public function testInvokeCreatesGenerator(): void
    {
        $injector = new Injector();
        $factory  = new GeneratorFactory();

        $result = $factory->create($injector->getContainer());
        $this->assertInstanceOf(InjectorGenerator::class, $result);
    }

    public function testFactoryUsesDiConfigContainer(): void
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMockForAbstractClass();
        $container->method('has')->willReturnCallback(static fn($type): bool => $type === ConfigInterface::class);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with(ConfigInterface::class)
            ->willReturn(new Config());

        $factory = new GeneratorFactory();
        $factory->create($container);
    }

    public function testSetsOutputDirectoryFromConfig(): void
    {
        $vfs       = vfsStream::setup(uniqid('laminas-di'));
        $expected  = $vfs->url();
        $container = new ServiceManager();
        $container->setService('config', [
            'dependencies' => [
                'auto' => [
                    'aot' => [
                        'directory' => $expected,
                    ],
                ],
            ],
        ]);

        $generator = (new GeneratorFactory())->create($container);
        $this->assertEquals($expected, $generator->getOutputDirectory());
    }

    public function testSetsNamespaceFromConfig(): void
    {
        $expected  = 'LaminasTest\\Di\\' . uniqid('Generated');
        $container = new ServiceManager();
        $container->setService('config', [
            'dependencies' => [
                'auto' => [
                    'aot' => [
                        'namespace' => $expected,
                    ],
                ],
            ],
        ]);

        $generator = (new GeneratorFactory())->create($container);
        $this->assertEquals($expected, $generator->getNamespace());
    }

    public function testDefaultLogger(): void
    {
        $generator  = (new GeneratorFactory())->create(new ServiceManager());
        $reflection = new ReflectionClass($generator);
        $property   = $reflection->getProperty('logger');
        $property->setAccessible(true);

        $this->assertInstanceOf(NullLogger::class, $property->getValue($generator));
    }

    public function testSetsLoggerFromConfig(): void
    {
        $logger    = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();
        $container = new ServiceManager();
        $container->setService('MyCustomLogger', $logger);
        $container->setService('config', [
            'dependencies' => [
                'auto' => [
                    'aot' => [
                        'logger' => 'MyCustomLogger',
                    ],
                ],
            ],
        ]);

        $generator  = (new GeneratorFactory())->create($container);
        $reflection = new ReflectionClass($generator);
        $property   = $reflection->getProperty('logger');
        $property->setAccessible(true);

        $this->assertNotInstanceOf(NullLogger::class, $property->getValue($generator));
    }

    public function testInvokeCallsCreate(): void
    {
        $mock = $this->getMockBuilder(GeneratorFactory::class)
            ->setMethods(['create'])
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMockForAbstractClass();

        $mock->expects($this->once())
            ->method('create')
            ->with($container);

        $result = $mock($container);
        $this->assertInstanceOf(InjectorGenerator::class, $result);
    }
}
