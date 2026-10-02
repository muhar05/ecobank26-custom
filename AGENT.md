# EcoBank026 Agent Instructions

## Mandatory
Before working on any task:
1. Read the relevant instructions in `.agents/`.
2. Follow applicable `.agents/` rules together with this file.
3. Keep changes scoped to the user's request.

## Execution
- Execute safe in-scope tasks directly.
- Do not ask for approval for routine safe changes.
- Do not modify unrelated business logic.

## QA
- Run only relevant tests/checks.
- Do not use Playwright, browser smoke tests, screenshots, Chrome headless, or `/tmp`
  unless explicitly requested or genuinely required.
- Do not import real user data/Excel unless explicitly requested.

## Documentation
- Do not create documentation unless explicitly requested.

## Data Safety
- Never reset or delete `users` unless explicitly requested.
- Preserve existing business rules and historical data unless the task explicitly requires changes.