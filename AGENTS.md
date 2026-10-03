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

# Project Agent Instructions

Before working on this project:

1. Read `DESIGN.md`.
2. Read relevant instructions/skills from `.agents/`.
3. For UI tasks, use the project's `.agents/antislop` and `.agents/antislop-ui` instructions.
4. Do not access configuration or files outside this project workspace unless explicitly requested.
5. Do not access `~/.config/antislop`.
6. Execute safe in-scope changes directly.
7. Do not create unnecessary tests, screenshots, browser smoke tests, docs, or temporary files unless requested or needed.
8. Preserve existing business logic and project conventions.