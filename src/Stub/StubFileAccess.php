<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Platform\AgentFileAccessInterface;
use Leoness\AgentSdk\Platform\StoredFileHandleInterface;

/**
 * File access a test seeds by hand: which handles the "current user" may read. Anything
 * not seeded is null, exactly like the platform refusing to say whether the file exists.
 */
final readonly class StubFileAccess implements AgentFileAccessInterface
{
    /**
     * @param list<StoredFileHandleInterface> $readable what findForCurrentUser() may return
     */
    public function __construct(
        private array $readable = [],
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
}
