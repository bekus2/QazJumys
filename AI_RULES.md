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
- Keep `public/` as the only web-accessible root.
- Keep PHP application logic in `app/`.
- Keep SQL files in `database/`.
- Use repositories for database access.
- Use `public/ajax.php` only for AJAX state changes.
- Do not introduce a framework without explicit approval.

## Coding Style Rules
- Keep source files documented with project headers.
- Add short useful comments for important logic in English and Russian.
- Keep public UI text in Kazakh unless documentation requires another language.
- Use readable PHP 8.3 syntax.
- Use semantic HTML.
- Keep CSS responsive and maintainable.

## Security Rules
- Never commit `.env`, real secrets, API keys, SMTP passwords, or tokens.
- Never store plaintext passwords.
- Use `password_hash()` and `password_verify()`.
- Use CSRF validation for state-changing requests.
- Use prepared statements for SQL.
- Escape output with `e()`.
- Keep private folders out of direct public access.

## Documentation Rules
When changing behavior, update the relevant sections of:
- `README.md`
- `HANDOFF.md`
- `PROJECT_CONTEXT.md`
- `Codex_History.md`
- `TASK.md`
- `AI_RULES.md`

Do not rewrite all documentation unless the architecture changes.

## Prohibited Actions
- Do not copy text, assets, or brand identity from external marketplaces.
- Do not mention private design references in product files or project docs.
- Do not delete working code without explaining the reason.
- Do not change architecture without approval.
- Do not add unnecessary dependencies.
- Do not expose default credentials on public pages.

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
- Check responsive layout after frontend changes.
- Validate SQL changes before deployment.
- Note any environment limitations in the final report.

## Final Report Format
After each project update, report:
1. What changed.
2. Which files changed.
3. How to install, run, and test.
4. Security measures added or preserved.
5. Documentation updated.
6. Remaining improvements.
7. Risks or limitations.
