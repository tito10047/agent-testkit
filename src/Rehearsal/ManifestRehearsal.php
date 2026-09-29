<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit\Rehearsal;

use Leoness\AgentSdk\Dto\AgentManifest;
use Leoness\AgentSdk\Validation\RuleExpressionLanguage;
use Leoness\AgentSdk\Validation\RuleFunctions;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Validator;

/**
 * A dry run of the manifest's own quality gates against a hand-written sample answer.
 *
 * The deployability test proves the manifest is *installable*; this proves it is
 * *coherent* — that an answer the author considers good passes their own schema and
 * their own rules, before a single token is paid for. It is the same idea as the
 * rehearsal in the platform's agent builder, minus the model.
 */
final readonly class ManifestRehearsal
{
    public function __construct(
        private RuleExpressionLanguage $expressions = new RuleExpressionLanguage(new RuleFunctions()),
    ) {
    }

    /**
     * Why the sample answer would be rejected, or nothing.
     *
     * @param array<string, mixed> $output the parsed structured answer being rehearsed
     * @param array<string, mixed> $input  what the run would have been started with
     *
     * @return list<string>
     */
    public function problems(AgentManifest $manifest, array $output, array $input = []): array
    {
        $problems = $this->schemaProblems($manifest, $output);

        foreach ($manifest->validationRules as $rule) {
            $verdict = $this->expressions->evaluate($rule->expression, [
                'output' => $output,
                'text' => json_encode($output, \JSON_UNESCAPED_UNICODE) ?: '',
                'input' => $input,
            ]);

            if (true !== $verdict) {
                $problems[] = \sprintf('Pravidlo "%s" vzorovú odpoveď odmieta: %s', $rule->expression, $rule->message);
            }
        }

        return $problems;
    }

    /**
     * @param array<string, mixed> $output
     *
     * @return list<string>
     */
    private function schemaProblems(AgentManifest $manifest, array $output): array
    {
        if (null === $manifest->outputSchema) {
            return [];
        }

        $result = new Validator()->validate(Helper::toJSON($output), Helper::toJSON($manifest->outputSchema));
        $error = $result->error();

        if (null === $error) {
            return [];
        }

        $problems = [];

        foreach (new ErrorFormatter()->format($error) as $pointer => $messages) {
            foreach ($messages as $message) {
                $problems[] = \sprintf('Odpoveď nesedí so schémou na %s: %s', (string) $pointer, $message);
            }
        }

        return $problems;
    }
}
