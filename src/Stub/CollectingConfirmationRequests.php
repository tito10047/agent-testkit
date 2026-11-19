<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Confirmation\ConfirmationRequestsInterface;
use Leoness\AgentSdk\Confirmation\Dto\IssuedConfirmation;
use Leoness\AgentSdk\Confirmation\Dto\ProposedAction;

/**
 * Confirmation requests that keep what was proposed, because "the tool asked a person
 * instead of acting" is the assertion a test of a confirmable tool exists to make.
 *
 * Numbers proposals from 1 and hands out a link nobody can open; whether the payload is
 * enough to carry the action out is tested on the executor, with the same payload.
 */
final class CollectingConfirmationRequests implements ConfirmationRequestsInterface
{
    /** @var list<ProposedAction> */
    public array $proposed = [];

    public function __construct(
        private readonly \DateTimeImmutable $expiresAt = new \DateTimeImmutable('2030-01-01 12:00:00'),
    ) {
    }

    public function request(ProposedAction $action): IssuedConfirmation
    {
        $this->proposed[] = $action;
        $id = \count($this->proposed);

        return new IssuedConfirmation($id, \sprintf('https://platform.test/potvrdenie/%d', $id), $this->expiresAt);
    }

    /**
     * The one proposal a test expects, so that a second, unexpected one fails it.
     */
    public function single(): ProposedAction
    {
        if (1 !== \count($this->proposed)) {
            throw new \LogicException(\sprintf('Expected exactly one proposed action, got %d.', \count($this->proposed)));
        }

        return $this->proposed[0];
    }
}
