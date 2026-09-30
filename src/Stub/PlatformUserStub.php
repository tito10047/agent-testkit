<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Platform\Entity\PlatformUserInterface;

/**
 * Somebody who approved, rejected or requested something — as much of an account as a
 * package is allowed to see.
 *
 * Exists because the alternative is what every domain package reaches for first: the core's
 * own `App\Security\Entity\User`. That works in the monorepo and nowhere else, so the test
 * passes right up until the package is checked out on its own — which is exactly the moment
 * it matters, because a green `composer check` in a clean clone is the condition for
 * installing the package at all.
 */
final class PlatformUserStub implements PlatformUserInterface
{
    public function __construct(
        private readonly string $name = 'Kontrolór',
        private readonly string $email = 'kontrolor@example.test',
        private readonly ?int $id = 1,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
