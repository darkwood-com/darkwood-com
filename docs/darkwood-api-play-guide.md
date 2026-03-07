# Playing Darkwood via the API

You can play Darkwood entirely through API calls. The game returns your current situation (where you are, your stats, combat state), and you send back what you want to do next (attack, use a potion, open a menu, etc.).

**Every request must include your API key** in the header:

```http
X-API-Key: <your-key>
```

To **play as a specific player** (your character, progress, combat), you also send a **JWT token** in the `Authorization` header. See **Authentication** below.

Replace `<your-key>` with the key you received. Use the API host you were given (for example `https://api.darkwood.example`).

---

## Authentication

There are **two separate things**:

- **API access** — Your **API key** (`X-API-Key`). Every Darkwood API request needs it. It controls whether you can call the API at all, and (for premium keys) access to archives and quota. Without a valid API key, requests are rejected (401 or 403).
- **Player login** — The **JWT (token)** identifies which **game account** is playing. Without it, the API may still respond, but the request is not tied to a player: you get `"user": null` and `"state": "not-logged"` and cannot play. For persistent player progression, you get a JWT from `POST /auth` and then send it on every Darkwood request.

So: **API key** = access to the API and premium features. **JWT** = which player account you are. For normal gameplay you need **both**.

### When each is needed

- **Without `X-API-Key`** — Darkwood API requests are rejected. You always need this header.
- **Without JWT** — You can still call the endpoints (e.g. `GET /api/darkwood/state`), but the server does not know which player you are. The response will have `"user": null` and `"state": "not-logged"` and you cannot play.
- **With both** — Send `X-API-Key: <your-key>` and `Authorization: Bearer <jwt-token>`. Then the server knows your API key and your player account; you get a proper game state and can play.

### Get a JWT token

Call `POST /auth` with your **email** and **password**. You do **not** need the API key for this step:

```bash
curl -i \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your-email@example.com",
    "password": "your-password"
  }' \
  https://YOUR_API_HOST/auth
```

On success, the response body contains the token (often in a `token` field). Copy that value and use it as `<jwt-token>` in the next step.

### Call Darkwood with both headers

For **authenticated gameplay** (main menu, combat, shops, etc.), send both headers on every request:

```bash
curl -s \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  "https://YOUR_API_HOST/api/darkwood/state"
```

Use the same two headers on `GET /api/darkwood/state` and `POST /api/darkwood/action` whenever you want to play as that player. The examples in this guide assume you are sending both when playing.

---

## Quick start

Two endpoints are enough to play. For **authenticated gameplay** (your character, progress), send both your API key and your JWT token on every request.

**1. Get your current state** (read where you are and what’s happening):

```bash
curl -s \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  "https://YOUR_API_HOST/api/darkwood/state"
```

You get back JSON with at least: `state`, `mode`, `user`, and sometimes extra `data` (e.g. your life, the current enemy, combat session).

**2. Do something** (perform a move or change screen):

```bash
curl -s -X POST \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":{"state":"main"}}' \
  "https://YOUR_API_HOST/api/darkwood/action"
```

**Difference in plain terms:**

- **`GET /api/darkwood/state`** — “What’s my situation right now?” Use it to see your current screen, stats, and combat info.
- **`POST /api/darkwood/action`** — “I’m doing this next.” Use it to attack, use a potion, open a menu, start a fight, or go to another screen.

You can also pass the same parameters as query string on the GET request (e.g. `?state=combat`) to both read state and ask for a specific screen in one call.

If you get `"state": "not-logged"` and `"user": null`, you are not authenticated as a player. Get a JWT from `POST /auth` (see **Authentication** above) and send it in the `Authorization: Bearer ...` header on every Darkwood request.

---

## How the game flow works

The flow is a loop:

1. **Ask for the current state** — Call `GET /api/darkwood/state` (optionally with `?state=...` to request a screen).
2. **Look at the response** — Check `state`, `mode`, and `data` to see where you are and what numbers matter (e.g. life, enemy life, gold).
3. **Send your next action** — Call `POST /api/darkwood/action` with a JSON body that includes a `query` object. In `query` you put the same kind of parameters: which screen you’re on, and which action you’re taking (e.g. attack, use potion, end fight).
4. **Repeat** — Use the new response as the new “current state” and decide the next action.

**Important:** What you’re allowed to do depends on the **current** `state` and `mode` in the response. For example, “attack” only works when you’re already in an active fight (`state=combat`, `mode=combat`). If you send an action that doesn’t match the current situation, the API will ignore it and just return the current state again. So always use the latest response to decide the next step.

---

## Common play steps

Below are the main situations you’ll run into and how to drive them with the API. Replace `YOUR_KEY`, `YOUR_JWT_TOKEN`, and `YOUR_API_HOST` with your API key, JWT token, and host. All gameplay examples use both headers.

### Main menu

When you’re on the main menu, the response has `"state": "main"` and usually no `data`. You can go to other screens by asking for them in the next request.

**Example — see main menu:**

```bash
curl -s \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  "https://YOUR_API_HOST/api/darkwood/state?state=main"
```

**Example — go to the combat screen (enemy selection):**

```bash
curl -s \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  "https://YOUR_API_HOST/api/darkwood/state?state=combat"
```

You should get `"state": "combat"` and a `data` object with `info` (your stats) and `currentEnemy` (the enemy you can fight and next/previous options).

---

### Starting a fight (PvE combat)

From the combat screen, you first choose an enemy (with next/previous if you want), then start the fight.

**Example — open combat and start a fight in one go:**

```bash
curl -s -X POST \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":{"state":"combat","actionBeginFight":"1"}}' \
  "https://YOUR_API_HOST/api/darkwood/action"
```

**How to know it worked:** The response should have `"state": "combat"`, `"mode": "combat"`, and a `data.session` object with things like `enemy_current_life`, `player_life_lose`, `enemy_life_lose`. That means the fight has started. If the enemy wasn’t allowed (e.g. above your progression), the server doesn’t start the fight; you stay on enemy selection and there is no error message in the response—you just don’t see `data.session` for an active fight.

---

### Fighting (one attack turn)

When you’re in an active fight (`state=combat`, `mode=combat`), you can attack once per request.

**Example — perform one attack:**

```bash
curl -s -X POST \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":{"state":"combat","mode":"combat","actionFight":"1"}}' \
  "https://YOUR_API_HOST/api/darkwood/action"
```

**How to know what happened:** Look at the response. `data.info.life` reflects your current life. `data.session` has `enemy_current_life` (enemy’s remaining life), `player_life_lose` (damage you took this turn), and `enemy_life_lose` (damage the enemy took). Use the updated numbers to decide whether to attack again, use a potion, or end the fight.

---

### Using a potion

During the same active fight, you can use a potion instead of attacking. You heal; then the enemy still attacks once. Potions are **not** available in daily battles—only in normal PvE combat.

**Example — use a potion:**

```bash
curl -s -X POST \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":{"state":"combat","mode":"combat","actionUsePotion":"1"}}' \
  "https://YOUR_API_HOST/api/darkwood/action"
```

**How to know what happened:** Check `data.info.life` again; it should go up (capped at your max). Then check `data.session` for the enemy’s hit on you (`player_life_lose`).

---

### Ending a fight

When you think someone has died (you or the enemy), you send “end fight.” The server then checks and applies win or loss.

**Example — end the fight:**

```bash
curl -s -X POST \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":{"state":"combat","mode":"combat","actionEndFight":"1"}}' \
  "https://YOUR_API_HOST/api/darkwood/action"
```

**How to know what happened:**

- If **you died**: The response has `"mode": "player_death"` and a `data.result` object with things like `lose_xp`, `lose_gold`, and `enemy`. Your character is reset (e.g. life restored to max) and the fight is over.
- If **the enemy died**: The response has `"mode": "player_win"` and `data.result` with e.g. `gem`, `level_up`, `enemy`. You get rewards and the fight is over.
- If **both are still alive**: The server does nothing; `mode` stays `"combat"` and you can keep attacking or using a potion.

After a win or loss, the next time you go to combat you’ll be back at enemy selection (no active fight until you send `actionBeginFight` again).

---

### Moving between menus (screens)

You change screen by sending the state you want. The response then shows that screen’s data.

**Examples:**

- Go to character info (stats, class, add points):
  `{"query":{"state":"info"}}`
- Go to equipment (gems):
  `{"query":{"state":"equipment"}}`
- Go to hostel (buy life with gold):
  `{"query":{"state":"hostel"}}`
- Go to armor shop:
  `{"query":{"state":"armor"}}`
- Go to potion shop:
  `{"query":{"state":"potion"}}`
- Go to sword shop:
  `{"query":{"state":"sword"}}`

**Example — open the info screen:**

```bash
curl -s -X POST \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":{"state":"info"}}' \
  "https://YOUR_API_HOST/api/darkwood/action"
```

The response will have `data.info` (your stats) and, for the info screen, `data.classes`. To add a stat point you’d send the same state plus an action, for example:
`{"query":{"state":"info","actionAddPoint":"strength"}}`
(use `"dexterity"` or `"vitality"` for other stats). That only works if you have points left; otherwise the server does nothing and doesn’t tell you in the response.

---

### Daily battle

Daily battle is a separate mode. You go there with `state=daily-battle`, start the fight with `actionBeginFight`, then use only `actionFight` (one exchange) and `actionEndFight` (resolve or keep fighting). There is **no potion** in daily battle—only fight and end fight.

**Example — start a daily fight:**

```bash
curl -s -X POST \
  -H "X-API-Key: YOUR_KEY" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"query":{"state":"daily-battle","actionBeginFight":"1"}}' \
  "https://YOUR_API_HOST/api/darkwood/action"
```

Win/loss is again indicated by `mode`: `player_win` or `player_death`, with details in `data.result`.

---

## Understanding the response

Responses are JSON. These are the fields that matter for playing.

**Always present:**

- **`state`** — The screen or situation you’re in (e.g. `main`, `combat`, `info`, `armor`). Use it to know where you are and what actions are valid.
- **`mode`** — A sub-phase. In combat, for example: no active fight vs fight in progress vs just won/lost. So you need both `state` and `mode` to know if you can attack or end the fight.
- **`user`** — Your user ID (a number), or `null` if you’re not authenticated as a player. When it’s `null`, you’ll see `state: "not-logged"` and you can’t play until you send a valid JWT in the `Authorization: Bearer ...` header.
- **`display`** — Device type (e.g. `web`). You can usually ignore it.

**When present, `data` holds the details for that screen:**

- **`data.info`** — Your character summary: life (`life.min`, `life.max`), damage range (`damage.min`, `damage.max`), points, armor, etc. Shown in combat, menus, and daily battle.
- **`data.session`** — During an active fight (PvE or daily): current life values and how much damage was done last turn (e.g. `enemy_current_life`, `player_life_lose`, `enemy_life_lose`). Lets you see fight progress.
- **`data.currentEnemy`** — On the combat screen when you’re **not** in a fight: the enemy you have selected and next/previous (as IDs). Lets you browse and then start a fight.
- **`data.result`** — Only right after a fight ends (win or loss). Tells you what you gained or lost: e.g. `lose_xp`, `lose_gold` on death; `gem`, `level_up`, `enemy` on win.

The API returns many IDs (user, player, enemy, armor, etc.) rather than names. So you’ll see numbers where you might expect labels; that’s how the API is built.

---

## Invalid or ignored actions

The API does **not** return a clear “invalid action” error. Instead:

- **Wrong screen:** If you send an action that only makes sense on another screen (e.g. “attack” while you’re on the main menu), that action is **ignored**. The response is the same as if you had only asked for the current state—no change, no error message.
- **Only one action per request:** If you send several actions in one request, the server only applies the **first** one it recognizes. The rest are ignored.
- **No effect:** Some actions only work when conditions are met (e.g. add stat point only when you have points left; start fight only when the enemy is allowed). When the condition isn’t met, the server does nothing—no error, no change in the response. You have to infer from the response (e.g. stats unchanged) that the action didn’t apply.
- **Typos in state:** If you send an unknown state name but you are logged in, the server treats it as the main menu and returns `state: "main"`.

So: always use the **latest** response to decide the next move, and if something doesn’t change as you expected, assume the action was ignored or not applicable. This behavior is not always explicit in the API; it comes from how the server is implemented.

---

## Premium archives

If you have a **premium** API key, you can read archived snapshots of game state. These are **read-only**: you can look at past states, but you cannot “restore” a game or play from an archive.

**List available archives:**

```bash
curl -s -H "X-API-Key: YOUR_PREMIUM_KEY" \
  "https://YOUR_API_HOST/api/darkwood/archives"
```

The response looks like:
`{"archives":[{"id":"2026-03-07","date":"2026-03-07"}, ...]}`
Each item has an `id` (and `date`, same value) you can use to fetch that day’s snapshot.

**Get one snapshot by date:**

```bash
curl -s -H "X-API-Key: YOUR_PREMIUM_KEY" \
  "https://YOUR_API_HOST/api/darkwood/archives/2026-03-07"
```

The body is the stored snapshot: same shape as a normal “state” response (e.g. `user`, `state`, `mode`, `data`). So it’s a point-in-time view of the game, not a live session.

If your key is not premium, these endpoints respond with **403** and a message that premium access is required.

---

## Error handling

When something goes wrong, the API returns an HTTP status code and a JSON body. Common cases:

**401 Unauthorized — Missing or invalid API key**

- You didn’t send `X-API-Key`, or the key is wrong.
- Response example: `{"error": "A valid API key is required"}`

**403 Forbidden**

- **Inactive key:** Your key exists but has been deactivated.
  Example: `{"error": "API key is inactive"}`
- **Beta access required:** Your key is valid but not allowed for the beta API.
  Example: `{"error": "Beta access required"}`
- **Premium required:** You called an archives endpoint without a premium key.
  Example: `{"error": "premium_required", "message": "Premium access required"}`

**429 Too many requests — Daily action limit**

- You’ve hit the daily limit for **POST /api/darkwood/action** (non-premium keys may have a limit). Premium keys are not limited.
- Response example: `{"error": "rate_limited", "message": "Daily action limit reached"}`
- The response may include a `Retry-After` header (in seconds) telling you when you can try again (typically after midnight UTC).

**400 Bad request — Invalid JSON**

- You sent a POST to `/api/darkwood/action` with a body that isn’t valid JSON.
- Response example: `{"error": "invalid_json", "message": "Request body must be valid JSON"}`

**404 Not found — Archive**

- You requested an archive that doesn’t exist (e.g. wrong date).
  Example: `{"error": "archive_not_found", "message": "Archive not found"}`

In all these cases, the JSON body has at least an `error` (and often a `message`) you can show to the user or use in your app.

---

## Practical tips

1. **Send both headers for gameplay.** For persistent player progression, send `X-API-Key` and `Authorization: Bearer <jwt>` on every request to `/api/darkwood/state` and `/api/darkwood/action`. Get the JWT once from `POST /auth`, then reuse it until it expires.
2. **Always use the latest response** to decide the next action. The current `state` and `mode` tell you what you can do; don’t assume the previous step succeeded without checking.
3. **Treat `data` as depending on the screen.** Not every response has `data`; when it does, the contents depend on `state` (and sometimes `mode`). For example, `data.session` only appears during an active fight.
4. **Send one action per request** when you want a specific move (attack, potion, end fight, etc.). If you send several action keys, only the first one that matches the current state is applied.
5. **When in combat, include both `state` and `mode`** in your action request. For example use `"state":"combat","mode":"combat"` when attacking or using a potion so the server knows you’re in the fight.
6. **Store archive IDs (dates)** if you want to revisit snapshots later. The list endpoint gives you `id`/`date`; use that in `GET /api/darkwood/archives/{id}`.
7. **If nothing changes after an action**, the action may have been ignored (wrong state) or not applicable (e.g. no points left, enemy not allowed). The API does not return a separate “action failed” message; you infer from the unchanged response.
