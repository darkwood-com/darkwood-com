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

Store the generated client secret only in deployment secrets / `.env.local`.

## Identity model

- Internal PK: integer `user.id` (unchanged)
- Public subject: `user.uuid` (UUIDv7, immutable)
- Login identifier: email / username (unchanged)
- Sessions: existing Symfony session + remember-me on `COOKIE_DOMAIN`
