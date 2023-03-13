<?php

declare(strict_types=1);

namespace Laminas\Di;

/**
 * Provides the instance and resolver configuration
 */
interface ConfigInterface
{
    /**
     * Check if the provided type name is aliased
     */
    public function isAlias(string $name): bool;

    /**
     * @return string[]
     */
    public function getConfiguredTypeNames(): array;

    /**
     * Returns the actual class name for an alias
     *
     * @return class-string|null
     */
    public function getClassForAlias(string $name): ?string;

    /**
     * Returns the instantiation parameters for the given type
     *
     * @param  string $type The alias or class name
     * @return array<array-key, mixed> The configured parameters
     */
    public function getParameters(string $type): array;

    /**
     * Set the instantiation parameters for the given type
     *
     * @param array<array-key, mixed> $params
     * @return mixed
     */
    public function setParameters(string $type, array $params);

    /**
     * Configured type preference
     */
    public function getTypePreference(string $type, ?string $contextClass = null): ?string;
}
