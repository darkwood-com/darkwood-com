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
