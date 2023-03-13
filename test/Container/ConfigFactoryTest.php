<?php

declare(strict_types=1);

namespace LaminasTest\Di\Container;

use Laminas\Di\ConfigInterface;
use Laminas\Di\Container\ConfigFactory;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function restore_error_handler;
use function set_error_handler;
use function strstr;
use function uniqid;

use const E_USER_DEPRECATED;

/** @covers \Laminas\Di\Container\ConfigFactory */
class ConfigFactoryTest extends TestCase
{
    /** @var MockBuilder<ContainerInterface> */
    private $containerBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->containerBuilder = $this->getMockBuilder(ContainerInterface::class);
    }

    protected function tearDown(): void
    {
        $this->containerBuilder = null;

        parent::tearDown();
    }

    public function testInvokeCreatesConfigInstance()
    {
        $container = $this->containerBuilder->getMockForAbstractClass();
        $container->method('has')->willReturn(false);

        $factory = new ConfigFactory();
        $this->assertInstanceOf(ConfigInterface::class, $factory($container));
    }

    /**
     * The factory must succeed even if the container does not provide "config"
     */
    public function testCreateRequestsContainerForConfigServiceGracefully()
    {
        $container = $this->containerBuilder->getMockForAbstractClass();
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $container->expects($this->never())
            ->method('get')
            ->with('config');

        $result = (new ConfigFactory())->create($container);
        $this->assertInstanceOf(ConfigInterface::class, $result);
    }

    private function createContainerWithConfig(array $config): ContainerInterface
    {
        $container = $this->containerBuilder->getMockForAbstractClass();
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $container->expects($this->atLeastOnce())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        return $container;
    }

    public function testCreateUsesConfigFromContainer()
    {
        $expectedPreference = uniqid('SomePreference');
        $container          = $this->createContainerWithConfig([
            'dependencies' => [
                'auto' => [
                    'preferences' => [
                        'SomeDependency' => $expectedPreference,
                    ],
                ],
            ],
        ]);

        $result = (new ConfigFactory())->create($container);
        $this->assertEquals($expectedPreference, $result->getTypePreference('SomeDependency'));
    }

    public function testLegacyConfigIsRespected()
    {
        $expectedPreference = uniqid('SomePreference');
        $container          = $this->createContainerWithConfig([
            'di' => [
                'instance' => [
                    'preferences' => [
                        'SomeDependency' => $expectedPreference,
                    ],
                ],
            ],
        ]);

        set_error_handler(static function ($errno, $errstr) {
            if ($errno !== E_USER_DEPRECATED) {
                return false;
            }

            if (! strstr($errstr, 'legacy DI config')) {
                // Not the error we're looking for...
                return false;
            }
        }, E_USER_DEPRECATED);
        $result = (new ConfigFactory())->create($container);
        restore_error_handler();

        $this->assertEquals($expectedPreference, $result->getTypePreference('SomeDependency'));
    }

    public function testLegacyConfigTriggersDeprecationNotice(): void
    {
        $container = $this->createContainerWithConfig([
            'di' => [
                'instance' => [],
            ],
        ]);

        $this->expectDeprecation();
        (new ConfigFactory())->create($container);
    }
}
