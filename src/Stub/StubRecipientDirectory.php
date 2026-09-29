<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Platform\RecipientDirectoryInterface;

/**
 * A recipient directory with exactly the administrators the test says exist —
 * including none, which is the case worth testing most.
 */
final readonly class StubRecipientDirectory implements RecipientDirectoryInterface
{
    /**
     * @param list<string> $emails
     */
    public function __construct(
        private array $emails = [],
    ) {
    }

    public function activeAdministratorEmails(): array
    {
        return $this->emails;
    }
}
