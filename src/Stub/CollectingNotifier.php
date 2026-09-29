<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

/**
 * A notifier that keeps what it was given. The channels themselves are Symfony's problem;
 * what an agent test is about is whether the agent asks them to do anything at all.
 */
final class CollectingNotifier implements NotifierInterface
{
    /** @var list<Notification> */
    public array $sent = [];

    /** @var list<list<RecipientInterface>> */
    public array $recipients = [];

    public function send(Notification $notification, RecipientInterface ...$recipients): void
    {
        $this->sent[] = $notification;
        $this->recipients[] = array_values($recipients);
    }
}
