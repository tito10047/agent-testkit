<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Platform\AgentFileAccessInterface;
use Leoness\AgentSdk\Platform\StoredFileHandleInterface;

/**
 * File access a test seeds by hand: which handles the "current user" may read, and
 * which a given run handed over. Anything not seeded is null, exactly like the
 * platform refusing to say whether the file exists.
 */
final class StubFileAccess implements AgentFileAccessInterface
{
    /**
     * @param list<StoredFileHandleInterface>             $readable   what findForCurrentUser() may return
     * @param array<int, list<StoredFileHandleInterface>> $runOutputs handles by run id
     */
    public function __construct(
        private array $readable = [],
        private array $runOutputs = [],
    ) {
    }

    public function findForCurrentUser(int $fileId): ?StoredFileHandleInterface
    {
        foreach ($this->readable as $handle) {
            if ($handle->id() === $fileId) {
                return $handle;
            }
        }

        return null;
    }

    public function findRunOutput(int $runId, int $fileId): ?StoredFileHandleInterface
    {
        foreach ($this->runOutputs[$runId] ?? [] as $handle) {
            if ($handle->id() === $fileId) {
                return $handle;
            }
        }

        return null;
    }
}
