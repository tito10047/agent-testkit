<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\FinishedRunInterface;
use Leoness\AgentSdk\RunFileView;
use Leoness\AgentSdk\RunStatus;
use Leoness\AgentSdk\RunTrigger;

/**
 * A finished run an agent test can hold in its hand.
 *
 * The named constructors mirror the three terminal states, because that is the first
 * thing every `handleFinishedRun()` implementation branches on. Everything else has a
 * plain default so a test only spells out what it is about.
 */
final readonly class FinishedRunStub implements FinishedRunInterface
{
    /**
     * @param array<string, mixed>      $inputSnapshot
     * @param array<string, mixed>|null $output
     * @param list<RunFileView>         $files
     * @param list<RunFileView>         $inputFiles
     */
    private function __construct(
        private int $id,
        private string $agentCode,
        private RunStatus $status,
        private RunTrigger $trigger,
        private array $inputSnapshot,
        private ?array $output,
        private ?string $outputText,
        private ?string $errorMessage,
        private ?\DateTimeImmutable $finishedAt,
        private array $files,
        private array $inputFiles = [],
    ) {
    }

    /**
     * @param array<string, mixed> $output
     * @param array<string, mixed> $input
     * @param list<RunFileView>    $files
     * @param list<RunFileView>    $inputFiles what the question carried, when the agent checks its result against it
     */
    public static function succeeded(
        array $output = [],
        array $input = [],
        RunTrigger $trigger = RunTrigger::Manual,
        ?string $outputText = null,
        array $files = [],
        int $id = 1,
        string $agentCode = 'agent',
        array $inputFiles = [],
    ): self {
        return new self($id, $agentCode, RunStatus::Succeeded, $trigger, $input, $output, $outputText, null, new \DateTimeImmutable('2026-01-01 08:00:00'), $files, $inputFiles);
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function failed(
        string $errorMessage = 'Zlyhanie modelu.',
        array $input = [],
        RunTrigger $trigger = RunTrigger::Manual,
        int $id = 1,
        string $agentCode = 'agent',
    ): self {
        return new self($id, $agentCode, RunStatus::Failed, $trigger, $input, null, null, $errorMessage, new \DateTimeImmutable('2026-01-01 08:00:00'), []);
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function cancelled(
        array $input = [],
        RunTrigger $trigger = RunTrigger::Batch,
        int $id = 1,
        string $agentCode = 'agent',
    ): self {
        return new self($id, $agentCode, RunStatus::Cancelled, $trigger, $input, null, null, null, null, []);
    }

    public function id(): int
    {
        return $this->id;
    }

    public function agentCode(): string
    {
        return $this->agentCode;
    }

    public function status(): RunStatus
    {
        return $this->status;
    }

    public function trigger(): RunTrigger
    {
        return $this->trigger;
    }

    public function inputSnapshot(): array
    {
        return $this->inputSnapshot;
    }

    public function output(): ?array
    {
        return $this->output;
    }

    public function outputText(): ?string
    {
        return $this->outputText;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function finishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function inputFiles(): array
    {
        return $this->inputFiles;
    }
}
