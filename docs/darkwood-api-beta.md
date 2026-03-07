# Darkwood API Premium - Beta Season 0

## Authentication

Darkwood API uses **two separate mechanisms**:

1. **API access** — Every request must include your API key in the header:  
   `X-API-Key: <your-key>`  
   This controls whether you can call the API at all, and (for premium keys) access to archives and quota.

2. **Player authentication** — To play as a specific game account (persistent progression, combat, etc.), you must authenticate the **player** using a **JWT (JSON Web Token)**. The JWT is obtained from `POST /auth` and then sent on Darkwood gameplay requests.

**For normal authenticated gameplay**, send **both** headers:

- `X-API-Key: <your-api-key>`
- `Authorization: Bearer <jwt-token>`

Without `X-API-Key`, Darkwood API requests are rejected (401/403). Without a JWT, the API may still respond (e.g. you can call `/api/darkwood/state`), but the request is not tied to a player account—you will get `"user": null` and `"state": "not-logged"` and cannot play. For persistent player progression, authenticate via `POST /auth` and send the JWT on every Darkwood request.

### Get a JWT token

Send your **email** and **password** to `POST /auth` (no API key required for this step):

```bash
curl -i \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your-email@example.com",
    "password": "your-password"
  }' \
  http://api.darkwood.localhost/auth
```

On success, the response body contains the token (format depends on the server; often a JSON object with a `token` field). Use that value as `<jwt-token>` in the `Authorization: Bearer ...` header.

### Call Darkwood with both headers

Example: get current game state as an authenticated player:

```bash
curl -s \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://api.darkwood.localhost/api/darkwood/state
```

Use the same two headers on every request to `GET /api/darkwood/state` and `POST /api/darkwood/action` when you want to play as that player.

## Endpoints

- `GET /api/darkwood/state`
- `POST /api/darkwood/action`
- `GET /api/darkwood/archives` (premium only)
- `GET /api/darkwood/archives/{id}` (premium only)

## Examples

Get state (authenticated player):

```bash
curl -i \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://api.darkwood.localhost/api/darkwood/state
```
