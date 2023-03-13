<?php

declare(strict_types=1);

namespace LaminasTest\Di;

use Laminas\Di\CodeGenerator\InjectorGenerator;
use Laminas\Di\ConfigInterface;
use Laminas\Di\ConfigProvider;
use Laminas\Di\InjectorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Laminas\Di\Module
 */
class ConfigProviderTest extends TestCase
{
    public function testInstanceIsInvokable(): void
    {
        $this->assertIsCallable(new ConfigProvider());
    }

    public function testProvidesDependencies(): void
    {
        $provider = new ConfigProvider();
        $result   = $provider();

        $this->assertArrayHasKey('dependencies', $result);
        $this->assertEquals($provider->getDependencyConfig(), $result['dependencies']);
    }

    /**
     * Provides service names that should be defined with a factory
     *
     * @return iterable<string, array{0: class-string}>
     */
    public function provideExpectedServicesWithFactory(): iterable
    {
        return [
            //               service name
            'injector'  => [InjectorInterface::class],
            'config'    => [ConfigInterface::class],
            'generator' => [InjectorGenerator::class],
        ];
    }

    /**
     * @dataProvider provideExpectedServicesWithFactory
     */
    public function testProvidesFactoryDefinition(string $serviceName): void
    {
        $result = (new ConfigProvider())->getDependencyConfig();

        $this->assertArrayHasKey($serviceName, $result['factories']);
    }
}
