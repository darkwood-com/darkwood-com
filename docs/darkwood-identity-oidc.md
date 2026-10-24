# Darkwood Identity (OIDC)

Darkwood is the canonical identity provider for the ecosystem. Applications authenticate users through OpenID Connect; they must not create parallel account stores.

## Endpoints

Issuer is the Darkwood site host (scheme + host, e.g. `https://darkwood.com` or `http://darkwood.localhost:8092`).

| Endpoint | Path |
|----------|------|
| Discovery | `GET /.well-known/openid-configuration` |
| Authorization | `GET /authorize` |
| Token | `POST /token` |
| JWKS | `GET /jwks` |
| End session | `GET\|POST /end-session` |

Supported scopes: `openid`, `profile`, `email`, `roles`.

ID token `sub` is the immutable user UUID (UUIDv7). Email is a claim, not an identity key. Custom claim `roles` carries Symfony roles when the `roles` scope is granted.

## Environment

```env
OAUTH_PRIVATE_KEY=%kernel.project_dir%/config/oidc/private.key
OAUTH_PUBLIC_KEY=%kernel.project_dir%/config/oidc/public.key
OAUTH_PASSPHRASE=
OAUTH_ENCRYPTION_KEY=<32-byte hex or defuse key>
```

Generate keys:

```bash
mkdir -p config/oidc
openssl genrsa -out config/oidc/private.key 2048
openssl rsa -in config/oidc/private.key -pubout -out config/oidc/public.key
chmod 600 config/oidc/private.key
```

OIDC signing keys are **separate** from Lexik JWT keys in `config/jwt/`.

## Client registration

```bash
php bin/console league:oauth2-server:create-client "Navi" navi \
  --redirect-uri="http://127.0.0.1:8000/admin/auth/callback" \
  --redirect-uri="https://navi.darkwood.com/admin/auth/callback" \
  --grant-type="authorization_code" \
  --grant-type="refresh_token" \
  --scope="openid" --scope="profile" --scope="email" --scope="roles"
```

```bash
php bin/console league:oauth2-server:create-client "Darkwaar" darkwaar \
  --redirect-uri="http://127.0.0.1:8000/auth/callback" \
  --redirect-uri="https://darkwaar.com/auth/callback" \
  --grant-type="authorization_code" \
  --grant-type="refresh_token" \
  --scope="openid" --scope="profile" --scope="email" --scope="roles"
```

## Relying parties

| App | Role | Notes |
|-----|------|-------|
| Navi | Admin RP | `ROLE_ADMIN` gate; local logout; default login theme |
| Darkwaar | Public consumer RP | Any authenticated user; local logout; **Darkwaar login theme** |

## Contextual login themes

When an unauthenticated browser hits `/authorize`, Symfony stores the full authorize URI in `_security.main.target_path` and redirects to `/login` (then optional `/2fa`).

The login/TOTP **visual theme** is derived only from a **validated** OAuth2 `client_id` in that authorize target:

1. Parse `client_id` from `_security.main.target_path` (path must be `/authorize`; host must be Darkwood or API host).
2. Look up the client with `ClientManagerInterface::find()` and require it active.
3. Map via server allowlist `oidc_login_themes` in `config/packages/oidc_login_themes.yaml`.

| OIDC client | Login theme |
|-------------|-------------|
| `darkwaar` | `darkwaar` |
| `navi` / unknown / missing | `default` (Darkwood) |
| Direct `/login` | `default` |

**Do not** trust `?theme=darkwaar` or any unvalidated query parameter for branding.

Resolved themes are stored in session key `app.oidc.login_theme` so the TOTP challenge keeps the same look. Direct login without an authorize target clears that key.

Presentation lives in `templates/common/pages/login.html.twig` and `two_factor_form.html.twig` plus `assets/styles/login-themes.css`. Authentication (password, TOTP, CSRF, sessions) is unchanged.

### Adding a future application theme

1. Register the OAuth2 client.
2. Add `client_id: theme_id` to `oidc_login_themes`.
3. Add CSS under `.login-theme-{theme_id}` and Twig branches as needed.
4. Place a logo under `public/common/images/oidc/` if required.

### Theme verification

1. Darkwaar OIDC login shows Darkwaar-branded login (and TOTP if enabled).
2. Direct Darkwood `/login` stays default.
3. Navi OIDC stays default.
4. `?theme=darkwaar` on `/login` without a valid authorize target does **not** switch branding.
5. Existing SSO session skips the login page and resumes `/authorize`.

## Identity model

- Internal PK: integer `user.id` (unchanged)
- Public subject: `user.uuid` (UUIDv7, immutable)
- Login identifier: email / username (unchanged)
- Sessions: existing Symfony session + remember-me on `COOKIE_DOMAIN`
