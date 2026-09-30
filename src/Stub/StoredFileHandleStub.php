<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Exception\FileUnreadableException;
use Leoness\AgentSdk\Platform\StoredFileHandleInterface;

/**
 * A stored file backed by a local path — what a package test hands to code that
 * expects the platform's file access.
 */
final readonly class StoredFileHandleStub implements StoredFileHandleInterface
{
    public function __construct(
        private int $id,
        private string $originalName,
        private string $path,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function originalName(): string
    {
        return $this->originalName;
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->originalName, \PATHINFO_EXTENSION));
    }

    public function withLocalCopy(callable $reader): mixed
    {
        if (!is_file($this->path)) {
            throw new FileUnreadableException(\sprintf('Súbor "%s" nemá obsah.', $this->originalName));
        }

        return $reader(new \SplFileInfo($this->path));
    }
}
