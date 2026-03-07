# Darkwood API Premium - Beta Season 0

## Authentication

All requests must include:

`X-API-Key: <your-key>`

## Endpoints

- `GET /api/darkwood/state`
- `POST /api/darkwood/action`
- `GET /api/darkwood/archives` (premium only)

## Examples

```bash
curl -i \
  -H "X-API-Key: YOUR_KEY" \
  http://api.darkwood.localhost/api/darkwood/state
