# leoness/agent-testkit

„Test schopnosti nasadenia“ pre agentové balíky Leoness AI. Beží **bez DB, bez
core a bez siete** — všetko pracuje len nad manifestom a stubmi SDK kontraktov.

## Ako sa používa

```php
final class SeoAuditorAgentTest extends AgentDeployabilityCase
{
    protected function agent(): AgentInterface
    {
        return new SeoAuditorAgent(
            new CollectingNotifier(),
            new StubRecipientDirectory(),
            new NullLogger(),
        );
    }

    protected function capabilities(): CapabilityMap
    {
        return new CapabilityMap(tools: ['najdi_produkt']);
    }
}
```

Zdedené final testy overia: manifest prejde `ManifestLinter`om (strict schéma,
parsovateľné pravidlá, prompt premenné ⊆ vstupné polia), prompt sa vyrenderuje
z deklarovaných vstupov, whitelisty ⊆ `CapabilityMap`, technicky znejúce polia
sú `FactClaim` a schéma prežije round-trip editora.

Logiku `handleFinishedRun()` testuj cez `Stub\FinishedRunStub` (`::succeeded()`,
`::failed()`, `::cancelled()`); notifikácie cez `Stub\CollectingNotifier`.

`Rehearsal\ManifestRehearsal` pustí vzorovú odpoveď proti schéme aj pravidlám —
dôkaz, že „dobrá odpoveď“ podľa autora prejde jeho vlastnými bránami.

## CI

`vendor/bin/agent-lint "Leoness\\Agent\\X\\XAgent"` — exit 1, keď by inštalácia
manifest odmietla.
