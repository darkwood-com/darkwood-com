# Changelog

## Unreleased

- Migrate app to Symfony 8 and PHP 8.5 (runtime, Nix, and CI).
- Refactor controllers routing from annotations to PHP attributes and update recipes/configuration.
- Integrate Darkwood API MCP tools (Api Platform MCP) for AI-powered workflows.
- Upgrade Darkwood IA Exception Bundle and fix related deprecations.
- Make Baserow optional and harden behavior when disabled; fix various production issues (SEO, Castor, user serialisation, reCAPTCHA).
- Apply Darkwood v4 UI across the site (menus, mobile navigation, login, footer, buttons, layout polish).

## v1.0.3

- Darkwood API: access and monetization via API keys only (X-API-Key, no env or User).
- Rate limit POST /api/darkwood/action by key; premium keys bypass. Archives: premium-only by key.
- Add `--limit` to darkwood:apikey:create. Simplify ApiKeyResolver.

## v1.0.2

- Add Darkwood IA Exception Bundle

## v1.0.1

- Add tests.
- Players must defeat the previous enemy before challenging the current one.
- Add new Api endpoints : Page, PageTranslation, Article, ArticleTranslation.

## v1.0.0

- Initial release.
