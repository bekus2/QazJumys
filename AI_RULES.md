# AI_RULES.md

Project: QazJumys
Version: 1.2.0
Author: Beck Sarbassov
Last updated: 2026-06-16

## Read Before Editing

Before code changes, read:

- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

## Architecture Rules

- Keep plain PHP architecture unless a new framework is explicitly approved.
- Keep public entry points in `public`.
- Keep private logic in `app`.
- Keep uploads in `storage/uploads`.
- Keep database access inside repositories.

## Coding Rules

- Maintain professional file headers.
- Use prepared statements for SQL.
- Validate server-side input.
- Escape HTML output with `e()`.
- Keep comments useful and brief.

## Security Rules

- Never commit `.env` or real secrets.
- Keep owner-only actions behind `Auth::isOwner()`.
- Keep CSRF on all state-changing POST actions.
- Do not allow direct public upload access.
- Do not store plaintext passwords.

## Documentation Rules

When behavior changes, update the relevant sections in:

- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

## Git/GitHub Workflow

- Work on a feature branch.
- Run tests before committing.
- Commit code and docs together.
- Push branch and merge to `main` only after verification.

## Testing

Run:

```powershell
C:\OSPanel\modules\php\PHP_8.1\php.exe tests\run.php
```

Also verify local OpenServer pages after database import.

## Final Report Format

Report:

1. What changed.
2. Files changed.
3. How to run/test.
4. Security preserved or added.
5. Documentation updated.
6. Known limitations.

Автор: Beck Sarbassov
Дата создания: 2026-06-16
Последнее обновление: 2026-06-16
Авторские права: © Beck Sarbassov. Все права защищены.
