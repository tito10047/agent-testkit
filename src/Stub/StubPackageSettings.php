<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Settings\Dto\SettingValues;
use Leoness\AgentSdk\Settings\PackageSettingsInterface;

/**
 * Package settings as a test sets them: the installation's values and, on top, the signed-in
 * user's own — the same layering the platform does, without a database.
 *
 * Give it **every** key the package declares, with the default where a test does not care:
 * the platform always resolves every declared key, so a package never sees "not set", and a
 * stub that let a key be missing would hide a typo in a key name until production.
 *
 * Usage: `new StubPackageSettings(['leoness/agent-emailovy' => ['eskalacny_limit_eur' => 1000.0]])`.
 */
final class StubPackageSettings implements PackageSettingsInterface
{
    /**
     * @param array<string, array<string, string|int|float|bool|list<string>>> $installation per package
     * @param array<string, array<string, string|int|float|bool|list<string>>> $user         per package, overrides for the current user
     */
    public function __construct(
        private array $installation = [],
        private array $user = [],
    ) {
    }

    /**
     * @param string|int|float|bool|list<string> $value
     */
    public function setInstallation(string $package, string $key, string|int|float|bool|array $value): void
    {
        $this->installation[$package][$key] = $value;
    }

    /**
     * @param string|int|float|bool|list<string> $value
     */
    public function setUser(string $package, string $key, string|int|float|bool|array $value): void
    {
        $this->user[$package][$key] = $value;
    }

    public function installation(string $package): SettingValues
    {
        return new SettingValues($package, $this->installation[$package] ?? []);
    }

    public function currentUser(string $package): SettingValues
    {
        return new SettingValues($package, [...$this->installation[$package] ?? [], ...$this->user[$package] ?? []]);
    }
}
