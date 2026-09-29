<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit;

/**
 * The tools and data sources an agent repository *claims* the platform will provide.
 *
 * A declaration, not a lookup: the repository cannot see the platform's registries, so
 * this map is the author writing down what the agent depends on. The deployability test
 * holds the manifest's whitelists against it — a tool the agent whitelists but never
 * declared here is a typo caught in CI instead of a Failed installation in the
 * administration. Whether the declared names actually exist is checked by the platform
 * at installation, where the registries are.
 */
final readonly class CapabilityMap
{
    /**
     * @param list<string> $tools
     * @param list<string> $dataSources
     */
    public function __construct(
        public array $tools = [],
        public array $dataSources = [],
    ) {
    }
}
