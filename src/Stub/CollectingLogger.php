<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Psr\Log\AbstractLogger;

/**
 * A logger that keeps its lines, because "the tool said so in the log" is an
 * assertion, not a side effect.
 */
final class CollectingLogger extends AbstractLogger
{
    /** @var list<string> */
    public array $records = [];

    public function log(mixed $level, \Stringable|string $message, array $context = []): void
    {
        $this->records[] = (string) $message;
    }
}
