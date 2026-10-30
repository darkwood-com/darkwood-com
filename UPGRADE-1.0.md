UPGRADE FROM 1.0.7 to 1.0.8
==============================

## Application dependencies

Update `composer.json` / `package.json`, then run:

```bash
composer update
npm update
```

Key constraint changes:

```diff
  "require": {
+     "mcp/sdk": "^0.8.1",
-     "symfony/ai-open-ai-platform": "^0.10.0",
+     "symfony/ai-open-ai-platform": "^0.12.0",
-     "symfony/mcp-bundle": "^0.10.0",
+     "symfony/mcp-bundle": "^0.14.1",
  }
```

Symfony components resolve to **8.1.10**. `darkwood/navi` and `darkwood/ia-exception-bundle` resolve to **8.1.6**.

Frontend: pin `browserslist` and update Encore / Babel within current majors (`@symfony/webpack-encore` **7.2.0**).

## MCP Bundle 0.13+ configuration

`symfony/mcp-bundle` **≥ 0.13** uses named servers. Replace the old flat `client_transports` / `http` config:

```yaml
mcp:
    servers:
        default:
            transports:
                http: true
                stdio: false
            http:
                path: /mcp
            session:
                store: file
                directory: '%kernel.cache_dir%/mcp'
                ttl: 3600
            registry: '*'
```

## Version caps (do not jump further yet)

- **`symfony/ai-*` / `symfony/mcp-bundle` 0.15**: blocked by `darkwood/ia-exception-bundle` (`symfony/ai-platform` `^0.12`).
- **`mcp/sdk` 0.9**: incompatible with API Platform MCP `SecureRegistry` (needs pin at **0.8.x** / mcp-bundle **0.14.x**).
- Majors deferred: `api-platform/core` 5, `google/recaptcha` 2, `vich/uploader-bundle` 3, `webpack-cli` 7.

## Continuous migration notes applied

- Flex recipes synced for framework / twig / monolog / etc. (`Kernel::getAllowedEnvs()`, `AGENTS.md` from framework recipe).
- Removed deprecated `league_oauth2_server.client.allow_plaintext_secrets` (ignored since league/oauth2-server-bundle 2.0).
- Dropped unused Dependabot noise under `public/admin/plugins` (bower/Grunt/tests/docs manifests).

Remaining vendor deprecations (not app-owned): VichUploader tagged iterators → `#[AsTaggedItem]` (upstream).

## Deploy

After pulling **v1.0.8**:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
bin/console cache:clear --env=prod
```

No new Doctrine migrations in this release.


UPGRADE FROM 1.0.6 to 1.0.7
==============================

## Application dependencies

Update `composer.json` constraints, then run:

```bash
composer update darkwood/* symfony/* symfony/ai-open-ai-platform symfony/mcp-bundle
```

```diff
  "require": {
-     "darkwood/ia-exception-bundle": "^8.0.13",
-     "darkwood/navi": "^8.0.13",
+     "darkwood/ia-exception-bundle": "^8.1",
+     "darkwood/navi": "^8.1",
-     "symfony/ai-open-ai-platform": "^0.8.0",
+     "symfony/ai-open-ai-platform": "^0.10.0",
-     "symfony/mcp-bundle": "^0.8.0",
+     "symfony/mcp-bundle": "^0.10.0",
-     "symfony/flex": "^2.4.0",
+     "symfony/flex": "^2.11",
  }
```

Symfony components should already be on `^8.1`; this release resolves them to **8.1.1**.

## Symfony AI

**1.0.7** requires Symfony AI **0.10** when using `symfony/ai-open-ai-platform` or
`symfony/mcp-bundle`. Bump every Symfony AI package you depend on in the same
`composer update` so Composer resolves a single `symfony/ai-platform` version.

## Telegram contact notifications (optional)

To receive a Telegram message when the contact form is submitted, set in `.env.local`:

```dotenv
TELEGRAM_DSN=telegram://TOKEN@default?channel=CHAT_ID
```

If `TELEGRAM_DSN` is unset, the notifier uses a null transport and the app behaves as before.

## Deploy

After pulling **v1.0.7**:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
bin/console doctrine:migrations:migrate --no-interaction
bin/console cache:clear --env=prod
```
