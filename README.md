# leoness/agent-testkit

Pomôcky na testovanie **balíkov MCP nástrojov** Leoness AI. Beží **bez DB, bez core
a bez siete** — všetko pracuje nad atribútmi nástrojov a stubmi SDK kontraktov.

## Deklarácia nástrojov

```php
final class ToolDeclarationTest extends ToolDeclarationCase
{
    protected function toolClasses(): array
    {
        return [RowCountTool::class];
    }
}
```

Zdedené final testy overia to isté, čo previerka balíka pri inštalácii: každý nástroj
má `#[AsTool]` s názvom v snake_case a neprázdnym popisom, je chránený
`#[IsGrantedTool('TOOL_<NÁZOV>')]` (oprávnenie = `TOOL_` + názov veľkými písmenami,
`ToolDeclarationLint::permissionFor()`), žiadna trieda nie je `App\*` a žiadne dva
nástroje nemajú rovnaký názov.

## Stuby portov SDK

- `Stub\StubFileAccess` + `Stub\StoredFileHandleStub` — súbory, ktoré „aktuálny
  používateľ“ smie čítať (ID ako z `priprav_nahratie`); čo nie je naseedované, je null.
- `Stub\CollectingConfirmationRequests` — zbiera `ProposedAction`, ktoré nástroj
  odovzdal na potvrdenie, a vracia číslo a odkaz. Test overí, že nástroj nekonal sám
  (`single()` vráti jediný návrh) a s tým istým payloadom potom zavolá vykonávateľa.
- `Stub\PlatformUserStub` — konto bez entity platformy.
- `Stub\CollectingLogger` — logger, ktorý si drží riadky.

## CI

`vendor/bin/agent-lint "Leoness\\Agent\\X\\Tool\\XTool" [...]` — exit 1, keď by previerka
deklaráciu nástroja odmietla.

## Verzia 3.0

Odobrané všetko agentové: `AgentDeployabilityCase`, `CapabilityMap`,
`Rehearsal\ManifestRehearsal`, `FinishedRunStub`, `CollectingRunAnnotator`,
`StubRecipientDirectory`, `CollectingNotifier`; `StubFileAccess` už nemá súbory behov.
Pribudli `ToolDeclarationCase`, `ToolDeclarationLint` a `CollectingConfirmationRequests`.
