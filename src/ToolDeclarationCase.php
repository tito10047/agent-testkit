<?php

declare(strict_types=1);

namespace Leoness\AgentTestkit;

use PHPUnit\Framework\TestCase;

/**
 * The test every tool package inherits: its tools are declared the way the platform's
 * vetting accepts — named, described, guarded by `TOOL_*` — and no two share a name.
 *
 * Runs without the platform, without a database and without a model: it reads attributes,
 * nothing else. What a tool *does* is the package's own tests'; this only makes sure the
 * installation will not refuse it for something a CI run could have said.
 */
abstract class ToolDeclarationCase extends TestCase
{
    /**
     * Every tool class the package ships.
     *
     * @return non-empty-list<class-string>
     */
    abstract protected function toolClasses(): array;

    final public function testEveryToolIsDeclaredTheWayThePlatformAccepts(): void
    {
        $lint = new ToolDeclarationLint();
        $problems = [];

        foreach ($this->toolClasses() as $class) {
            array_push($problems, ...$lint->problemsOf($class));
        }

        self::assertSame([], $problems, implode("\n", $problems));
    }

    final public function testNoTwoToolsShareAName(): void
    {
        $lint = new ToolDeclarationLint();
        $names = [];

        foreach ($this->toolClasses() as $class) {
            array_push($names, ...$lint->toolNames($class));
        }

        self::assertSame(array_values(array_unique($names)), $names, 'Dva nástroje balíka majú rovnaký názov.');
    }
}
