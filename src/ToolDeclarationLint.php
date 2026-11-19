<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit;

/**
 * What the platform's package vetting would refuse about a tool class, said before the
 * package ever reaches it.
 *
 * The rule is the platform's convention: every `#[AsTool]` is guarded by an
 * `#[IsGrantedTool]` whose permission is `TOOL_` and the tool name upper-cased. A tool
 * without it would be callable by anybody signed in to MCP — and the administration grants
 * tools by exactly that permission name, so a typo in it is a tool nobody can be given.
 *
 * Read from the attributes' arguments rather than by instantiating them, so the testkit
 * does not drag the AI bundle into every package's dev dependencies just to read two names.
 */
final readonly class ToolDeclarationLint
{
    public const string AS_TOOL = 'Symfony\AI\Agent\Toolbox\Attribute\AsTool';
    public const string IS_GRANTED_TOOL = 'Symfony\AI\AiBundle\Security\Attribute\IsGrantedTool';

    /** Snake case, the shape every platform tool name has (`najdi_produkt`). */
    private const string NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    /**
     * The permission a tool of this name has to be guarded by.
     */
    public static function permissionFor(string $toolName): string
    {
        return 'TOOL_'.strtoupper($toolName);
    }

    /**
     * Tool names the class declares, in declaration order.
     *
     * @param class-string $class
     *
     * @return list<string>
     */
    public function toolNames(string $class): array
    {
        $names = [];

        foreach (new \ReflectionClass($class)->getAttributes(self::AS_TOOL) as $attribute) {
            $names[] = self::argument($attribute, 'name', 0);
        }

        return $names;
    }

    /**
     * @param class-string $class
     *
     * @return list<string> one sentence per problem, empty when the class is fine
     */
    public function problemsOf(string $class): array
    {
        if (str_starts_with($class, 'App\\')) {
            return [\sprintf('%s je trieda platformy, nie balíka — balík smie siahať iba na Leoness\AgentSdk\*.', $class)];
        }

        $reflection = new \ReflectionClass($class);
        $tools = $reflection->getAttributes(self::AS_TOOL);

        if ([] === $tools) {
            return [\sprintf('%s nemá atribút #[AsTool].', $class)];
        }

        $granted = $this->grantedPermissions($reflection);
        $problems = [];

        foreach ($tools as $tool) {
            $name = self::argument($tool, 'name', 0);
            $description = self::argument($tool, 'description', 1);

            if (1 !== preg_match(self::NAME_PATTERN, $name)) {
                $problems[] = \sprintf('%s: názov nástroja "%s" nie je snake_case.', $class, $name);
            }

            if ('' === trim($description)) {
                $problems[] = \sprintf('%s: nástroj "%s" nemá popis — model by nevedel, kedy ho volať.', $class, $name);
            }

            if (!\in_array(self::permissionFor($name), $granted, true)) {
                $problems[] = \sprintf('%s: nástroj "%s" nie je chránený #[IsGrantedTool(\'%s\')].', $class, $name, self::permissionFor($name));
            }
        }

        return $problems;
    }

    /**
     * Permissions named by `#[IsGrantedTool]` on the class or on any of its methods — a
     * class exposing two methods as two tools guards each method separately.
     *
     * @param \ReflectionClass<object> $reflection
     *
     * @return list<string>
     */
    private function grantedPermissions(\ReflectionClass $reflection): array
    {
        $attributes = $reflection->getAttributes(self::IS_GRANTED_TOOL);

        foreach ($reflection->getMethods() as $method) {
            array_push($attributes, ...$method->getAttributes(self::IS_GRANTED_TOOL));
        }

        $permissions = [];

        foreach ($attributes as $attribute) {
            $permission = $attribute->getArguments()['attribute'] ?? $attribute->getArguments()[0] ?? null;

            if (\is_string($permission)) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * @param \ReflectionAttribute<object> $attribute
     */
    private static function argument(\ReflectionAttribute $attribute, string $name, int $position): string
    {
        $arguments = $attribute->getArguments();
        $value = $arguments[$name] ?? $arguments[$position] ?? '';

        return \is_string($value) ? $value : '';
    }
}
