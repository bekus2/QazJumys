# AI_RULES.md

Project: QazJumys
Version: 1.4.0
Author: Beck Sarbassov
Last updated: 2026-06-28

## Read Before Editing

Before changes, read:

- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

## Architecture Rules

- Keep the plain PHP architecture.
- Keep public entry points in `public`.
- Keep private application code in `app`.
- Keep uploads in `storage/uploads`.
- Keep database logic in repositories.
- Keep project/proposal workflow in `ProjectRepository.php`.
- Keep file visibility rules in `FileRepository.php`.

## Security Rules

- Never commit `.env`.
- Never publish real passwords, tokens, API keys, SMTP passwords, or owner credentials in public docs.
- Keep CSRF on all POST state changes.
- Keep owner actions behind owner session checks.
- Use prepared statements.
- Validate uploads before storage.
- Keep direct upload access denied.
- Serve files only through `download.php`.
- Preserve the split rules for `brief`, `proposal`, and `delivery` files.
- Preserve blocked access for `declined` and `withdrawn` proposals.

## Testing Rules

Run the lightweight CI test before commit:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\run.php
```

For local release readiness, also run:

```powershell
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\db_smoke.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\workflow_smoke.php
& "C:\OSPanel\modules\PHP-8.4\php.exe" tests\http_smoke.php http://127.0.0.1:8014
```

## Documentation Rules

When code behavior changes, update README, HANDOFF, PROJECT_CONTEXT, Codex_History, TASK, and AI_RULES. Keep documentation practical and do not include private credentials.

## Git Rules

- Inspect `git status` before staging.
- Stage only intended files.
- Commit code, tests, and docs together.
- Push to the requested GitHub repository/branch after verification.

## Final Report

Report changed features, changed files, tests run, security notes, documentation updates, deployment notes, and remaining limitations.

Author: Beck Sarbassov
Created: 2026-06-16
Last updated: 2026-06-28
Copyright: © Beck Sarbassov. All rights reserved.
