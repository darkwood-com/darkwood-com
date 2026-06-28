# Changelog

## v1.0.7

- Add creator blog section with cover image upload and article translations.
- Add newsletter unsubscribe flow and register new users to the newsletter.
- Send Telegram notification when a contact form is submitted (optional `TELEGRAM_DSN`).
- Enforce non-empty article slugs; remove legacy backward-compatibility code and watermark.
- Rename auto-article integration to watch; fix uploaded files for translated content.
- Upgrade to Symfony **8.1.1**, `darkwood/*` **^8.1**, and Symfony AI / MCP **^0.10.0**.
- Dependency and security updates (Dompurify, Twig, PHPUnit, and related tooling).

## v1.0.6

- Add blog release section, premium content, Mermaid integration, and markdown table rendering.
- Highlight latest article post; add API article reactions.
- Internal API integration for auto articles and newsletter subscribers.
- Add Navi to the project section; production and reCAPTCHA fixes; Rector pass.

## v1.0.5

- Add two-factor authentication (TOTP) with QR code.
- Add hello showreel landing page with soundtrack visualiser.
- Expose CV security as API and MCP tools; harden hello landing security.

## v1.0.4

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
