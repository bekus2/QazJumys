# AI_RULES.md

## Files to Read Before Changes
Before editing code, AI agents must read:
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

## Architecture Rules
- Keep `public/` as the only intended web-accessible root.
- Keep PHP application logic in `app/`.
- Keep SQL files in `database/`.
- Use repositories for database access.
- Use `public/ajax.php` only for AJAX state changes.
- Do not introduce a framework without explicit approval.
- Keep production-safe data in `database/seed.sql`.
- Keep local preview/demo accounts in `database/demo.sql`.

## Coding Style Rules
- Keep source files documented with project headers.
- Add short useful comments for important logic in English and Russian.
- Keep public UI text in Kazakh unless documentation requires another language.
- Use readable PHP 8.1+ syntax.
- Use semantic HTML.
- Keep CSS responsive and maintainable.
- Do not use viewport-scaled font sizes.
- Keep card radius at 8px or less.

## Security Rules
- Never commit `.env`, real secrets, API keys, SMTP passwords, or tokens.
- Never store plaintext production passwords.
- Use `password_hash()` and `password_verify()`.
- Use CSRF validation for state-changing requests.
- Use prepared statements for SQL.
- Escape output with `e()`.
- Keep private folders out of direct public access.
- Do not import `database/demo.sql` into production unless demo accounts are removed immediately.
- Use `--default-character-set=utf8mb4` when importing SQL.

## Documentation Rules
When changing behavior, update the relevant sections of:
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

Do not rewrite all documentation unless the architecture or project state changes enough to require it.

## Prohibited Actions
- Do not copy text, assets, or brand identity from external marketplaces.
- Do not mention private design references in product files or project docs.
- Do not delete working code without explaining the reason.
- Do not change architecture without approval.
- Do not add unnecessary dependencies.
- Do not expose default credentials on public pages.
- Do not mix local demo accounts into production-safe seed data.

## Git and GitHub Workflow
- Keep `main` stable.
- Use feature branches for larger future changes.
- Use clear commit messages.
- Update docs with code changes.
- Review affected files and risk before committing.

Recommended branch names:
- `feature/module-name`
- `fix/bug-name`
- `docs/update-name`
- `refactor/module-name`
- `security/security-fix-name`

## Testing and Verification Rules
- Run PHP syntax checks for changed PHP files.
- Verify main pages render.
- Verify OpenServer local URL when available: `http://localhost:8080/qazjumys/`.
- Check responsive layout after frontend changes.
- Validate SQL imports with `utf8mb4` and confirm Kazakh/Russian text is not corrupted.
- Validate AJAX login or another CSRF-protected action after auth changes.
- Note any environment limitations in the final report.

## Required Final Report Format
After each project update, report:
1. What changed.
2. Which files changed.
3. How to install, run, and test.
4. Security measures added or preserved.
5. Documentation updated.
6. Remaining improvements.
7. Risks or limitations.
