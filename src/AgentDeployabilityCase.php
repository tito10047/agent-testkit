<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit;

use Leoness\AgentSdk\AgentInputType;
use Leoness\AgentSdk\AgentInterface;
use Leoness\AgentSdk\Dto\OutputSchemaPlan;
use Leoness\AgentSdk\OutputFieldKind;
use Leoness\AgentSdk\Validation\ManifestLinter;
use Leoness\AgentSdk\Validation\TechnicalFieldLint;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;

/**
 * The test every agent repository runs before it may call itself deployable.
 *
 * Extend it, return the agent and the capabilities it expects, and CI answers the
 * question the platform would otherwise answer at installation time, in front of an
 * administrator: is this manifest something the core will accept. No database, no core,
 * no network — everything here works on the manifest alone.
 *
 * The checks are final on purpose. A repository that overrides one of them out of its
 * way has an agent the platform will refuse; the place to argue with a rule is the SDK,
 * where the platform reads the same one.
 */
abstract class AgentDeployabilityCase extends TestCase
{
    /** The agent under test, built with whatever stubs its constructor needs. */
    abstract protected function agent(): AgentInterface;

    /** What the author believes the platform provides. See {@see CapabilityMap}. */
    abstract protected function capabilities(): CapabilityMap;

    final public function testTheManifestHasNothingALinterCanObjectTo(): void
    {
        $problems = new ManifestLinter()->lint($this->agent()->manifest());

        self::assertSame([], array_map(strval(...), $problems), 'Manifest by inštalácia odmietla.');
    }

    final public function testThePromptRendersFromExactlyTheDeclaredInputs(): void
    {
        $manifest = $this->agent()->manifest();

        $variables = [];

        foreach ($manifest->inputFields as $field) {
            $variables[$field->name] = match ($field->type) {
                AgentInputType::Text, AgentInputType::LongText => 'vzorová hodnota',
                AgentInputType::Number => 7,
                AgentInputType::Boolean => true,
                AgentInputType::Url => 'https://example.test/produkt',
            };
        }

        $twig = new Environment(new ArrayLoader(['prompt' => $manifest->promptTemplate]), ['strict_variables' => true]);

        try {
            $rendered = $twig->render('prompt', $variables);
        } catch (Error $error) {
            self::fail(\sprintf('Prompt sa z deklarovaných vstupov nevyrenderuje: %s', $error->getMessage()));
        }

        self::assertNotSame('', trim($rendered), 'Prompt sa vyrenderoval naprázdno.');
    }

    final public function testTheWhitelistsStayWithinTheDeclaredCapabilities(): void
    {
        $manifest = $this->agent()->manifest();
        $capabilities = $this->capabilities();

        self::assertSame(
            [],
            array_values(array_diff($manifest->allowedTools, $capabilities->tools)),
            'Agent si whitelistuje nástroj, ktorý sám nedeklaroval v CapabilityMap.',
        );
        self::assertSame(
            [],
            array_values(array_diff($manifest->allowedDataSources, $capabilities->dataSources)),
            'Agent si whitelistuje dátový zdroj, ktorý sám nedeklaroval v CapabilityMap.',
        );
    }

    final public function testFieldsThatSoundTechnicalAreDeclaredAsClaims(): void
    {
        $plan = OutputSchemaPlan::fromSchema($this->agent()->manifest()->outputSchema);

        if (null === $plan) {
            // A hand-written schema the field editor cannot represent. The lint reads
            // fields, so there is nothing for it here — the schema rules themselves
            // were already enforced by the linter test above.
            $this->addToAssertionCount(1);

            return;
        }

        self::assertSame([], new TechnicalFieldLint()->check($plan), 'Technický údaj bez FactClaim tvaru — model si ho smie vymyslieť.');
    }

    final public function testAClaimFieldSurvivesTheEditorRoundTrip(): void
    {
        // fromSchema() re-emits and compares; a schema that comes back is a schema the
        // platform's builder can open without silently rewriting the agent's contract.
        $manifest = $this->agent()->manifest();

        if (null === $manifest->outputSchema) {
            $this->addToAssertionCount(1);

            return;
        }

        $plan = OutputSchemaPlan::fromSchema($manifest->outputSchema);

        if (null === $plan) {
            $this->addToAssertionCount(1);

            return;
        }

        self::assertSame(
            $manifest->outputSchema,
            $plan->toSchema(),
            'Schéma zložená z polí sa musí dať zreprodukovať — inak ju builder pri uložení prepíše.',
        );
    }

    /**
     * Kinds are re-exported so a subclass writing extra checks does not need to import
     * SDK internals it otherwise never touches.
     */
    final protected static function claimKind(): OutputFieldKind
    {
        return OutputFieldKind::Claim;
    }
}
