<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Stub;

use Leoness\AgentSdk\Mail\Dto\AttachmentInfo;
use Leoness\AgentSdk\Mail\Dto\AuthenticationResults;
use Leoness\AgentSdk\Mail\Dto\AuthVerdict;
use Leoness\AgentSdk\Mail\Dto\MessageFacts;
use Leoness\AgentSdk\Mail\Dto\MessageLink;
use Leoness\AgentSdk\Mail\Dto\SenderDomainKind;

/**
 * Facts about one incoming message, the way the platform would hand them to a
 * `ThreadAssessorInterface` — for a package's tests.
 *
 * Starts from an ordinary, fully verified mail from a company domain, so a test states
 * only what makes its case special: `->withAuthentication(dmarc: AuthVerdict::Fail)`,
 * `->withReplyTo('platby@podvod.example')`. Immutable; every `with…` returns a copy.
 */
final class MessageFactsBuilder
{
    private string $fromAddress = 'zakaznik@firma.example';
    private string $displayName = 'Ján Zákazník';
    private ?string $envelopeFrom = 'zakaznik@firma.example';
    private ?string $replyTo = null;
    private AuthenticationResults $authentication;
    private SenderDomainKind $senderDomainKind = SenderDomainKind::Other;
    private string $subject = 'Dopyt';
    private string $bodyExcerpt = 'Dobrý deň, …';
    /** @var list<MessageLink> */
    private array $links = [];
    /** @var list<AttachmentInfo> */
    private array $attachments = [];
    private \DateTimeImmutable $receivedAt;
    private bool $answeredByUs = false;
    private bool $repeatedFromSender = false;

    public function __construct()
    {
        $this->authentication = new AuthenticationResults(AuthVerdict::Pass, AuthVerdict::Pass, AuthVerdict::Pass);
        $this->receivedAt = new \DateTimeImmutable('2026-11-22 09:00');
    }

    public function from(string $address, string $displayName = '', SenderDomainKind $kind = SenderDomainKind::Other): self
    {
        $copy = clone $this;
        $copy->fromAddress = $address;
        $copy->displayName = $displayName;
        $copy->envelopeFrom = $address;
        $copy->senderDomainKind = $kind;

        return $copy;
    }

    public function withEnvelopeFrom(?string $address): self
    {
        $copy = clone $this;
        $copy->envelopeFrom = $address;

        return $copy;
    }

    public function withReplyTo(?string $address): self
    {
        $copy = clone $this;
        $copy->replyTo = $address;

        return $copy;
    }

    public function withAuthentication(
        AuthVerdict $spf = AuthVerdict::Pass,
        AuthVerdict $dkim = AuthVerdict::Pass,
        AuthVerdict $dmarc = AuthVerdict::Pass,
    ): self {
        $copy = clone $this;
        $copy->authentication = new AuthenticationResults($spf, $dkim, $dmarc);

        return $copy;
    }

    public function withContent(string $subject, string $bodyExcerpt): self
    {
        $copy = clone $this;
        $copy->subject = $subject;
        $copy->bodyExcerpt = $bodyExcerpt;

        return $copy;
    }

    public function withLink(string $text, string $href): self
    {
        $copy = clone $this;
        $copy->links[] = new MessageLink($text, $href);

        return $copy;
    }

    public function withAttachment(string $name, string $mimeType, int $size = 1_000): self
    {
        $copy = clone $this;
        $copy->attachments[] = new AttachmentInfo($name, $mimeType, $size);

        return $copy;
    }

    public function receivedAt(\DateTimeImmutable $at): self
    {
        $copy = clone $this;
        $copy->receivedAt = $at;

        return $copy;
    }

    public function answeredByUs(bool $answered = true): self
    {
        $copy = clone $this;
        $copy->answeredByUs = $answered;

        return $copy;
    }

    public function repeatedFromSender(bool $repeated = true): self
    {
        $copy = clone $this;
        $copy->repeatedFromSender = $repeated;

        return $copy;
    }

    public function build(): MessageFacts
    {
        return new MessageFacts(
            $this->fromAddress,
            $this->displayName,
            $this->envelopeFrom,
            $this->replyTo,
            $this->authentication,
            $this->senderDomainKind,
            $this->subject,
            $this->bodyExcerpt,
            $this->links,
            $this->attachments,
            $this->receivedAt,
            $this->answeredByUs,
            $this->repeatedFromSender,
        );
    }
}
