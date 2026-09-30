<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Platform\RunAnnotatorInterface;

/**
 * Collects the warnings an agent attaches to its runs, keyed by run id, so a test can
 * assert on the exact sentence a person would read.
 */
final class CollectingRunAnnotator implements RunAnnotatorInterface
{
    /** @var array<int, list<string>> */
    public private(set) array $warnings = [];

    public function warn(int $runId, string $warning): void
    {
        $this->warnings[$runId][] = $warning;
    }

    /**
     * @return list<string>
     */
    public function for(int $runId): array
    {
        return $this->warnings[$runId] ?? [];
    }
}
