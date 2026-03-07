# How to play Darkwood via API

This guide describes how to drive Darkwood gameplay using the Darkwood API. It is derived from the actual controller and service code; no behavior is assumed beyond what exists in the codebase.

---

## Prerequisites

- **Authentication**: Every request must include the header:
  ```http
  X-API-Key: <your-key>
  ```
  The key must be **active** and **beta-enabled**. Otherwise you get:
  - No key or invalid key: **401** — `{ "error": "A valid API key is required" }`
  - Inactive key: **403** — `{ "error": "API key is inactive" }`
  - Active but not beta: **403** — `{ "error": "Beta access required" }`

- **Base URL**: Use the API host (e.g. `https://api.darkwood.example` or `http://api.darkwood.localhost`). See `docs/darkwood-api-beta.md`.

- **Game user**: The API calls `GameService::play($request, null, $user)` where `$user` comes from the security token (session). If no user is logged in (e.g. API-only calls with no session), the response has `"user": null` and `"state": "not-logged"`. To get gameplay states (`main`, `combat`, `info`, etc.), a **logged-in game user** must be associated with the request. How that is done in your deployment (e.g. API key linked to a user, or separate login flow) is environment-specific; this guide describes behavior once the server provides a non-null user.

---

## 1. Session start

### 1.1 Getting initial state

**Request**

```http
GET /api/darkwood/state
X-API-Key: <your-key>
```

Optional query parameters (same as for POST body `query` below):

- `state` — desired state (default: `main`)
- `mode` — sub-mode (optional)
- `display` — `web` | `iphone` | `ipad` | `mac` (default: `web`)

Example: open the combat screen with enemy selection:

```http
GET /api/darkwood/state?state=combat
X-API-Key: <your-key>
```

**Response (200, JSON)**

The body is the normalized game state. At minimum you get:

```json
{
  "user": 123,
  "state": "main",
  "mode": null,
  "display": "web"
}
```

- **`user`**: Current user ID (integer) or `null` if not logged in.
- **`state`**: Current game state (see §4). Default is `main` when no `state` is sent; unknown state names are normalized to `main` when a user is logged in.
- **`mode`**: Sub-mode (e.g. combat phase, rank filter). Can be `null`.
- **`display`**: Echo of requested display or `web` if invalid.

If the server has an unfinished PvE fight for this user and you request any state other than combat with mode combat, the response is **forced** to `state: "combat"` and `mode: "fight_not_ended"` (GameService.php 1014–1016).

### 1.2 Determining current state and next actions

- Read **`state`** and **`mode`** from the JSON response.
- Use them to decide:
  - Which screen/flow the client is in.
  - Which actions are valid for the next request (see §4 and §5).
- When **`data`** is present, it contains state-specific payload (e.g. `data.info`, `data.session`, `data.currentEnemy`). See `docs/darkwood-api-response-structure.md` for full shapes.

No separate “available actions” list is returned; validity is defined by the code per state/mode (see §4).

---

## 2. Action loop

### 2.1 Sending actions

**Request**

```http
POST /api/darkwood/action
Content-Type: application/json
X-API-Key: <your-key>

{"query": { ... }}
```

The body must be **valid JSON**. The code only uses the **`query`** key (DarkwoodPostActionController 44–50):

```php
$queryParams = $payload['query'] ?? [];
if (!is_array($queryParams)) {
    $queryParams = [];
}
foreach ($queryParams as $key => $value) {
    $request->query->set((string) $key, $value);
}
```

So everything that drives `GameService::play()` is passed as **query parameters**. The same parameters can be sent:

- As **query string** on `GET /api/darkwood/state` (e.g. `?state=combat&actionBeginFight=1`), or
- Inside **`query`** in the JSON body of `POST /api/darkwood/action` (e.g. `{"query": {"state": "combat", "actionBeginFight": "1"}}`).

After merging, the service uses `$request->get('state', 'main')`, `$request->get('mode')`, `$request->get('actionFight')`, etc. So:

- **State/mode**: Set `state` and optionally `mode` in `query` to navigate and to put the server in the right branch.
- **Actions**: Set one action flag per request (e.g. `actionFight`, `actionBeginFight`). The code checks **presence** of the key (truthy); the value is not validated. Sending `"actionFight": ""` or `"actionFight": "1"` both trigger the action.

### 2.2 Request payload format (from code)

- **Required**: Valid JSON body for POST.
- **Used**: `payload['query']` must be an object. Each key/value is set as a request query parameter.
- **Invalid JSON**: Response **400** with `{ "error": "invalid_json", "message": "Request body must be valid JSON" }`.

Representative examples:

Navigate to main (no action):

```json
{"query": {"state": "main"}}
```

Navigate to combat (enemy selection):

```json
{"query": {"state": "combat"}}
```

Start a fight (combat enemy selection screen):

```json
{"query": {"state": "combat", "actionBeginFight": "1"}}
```

One attack turn (during active combat):

```json
{"query": {"state": "combat", "mode": "combat", "actionFight": "1"}}
```

Use potion then enemy attacks (during active combat):

```json
{"query": {"state": "combat", "mode": "combat", "actionUsePotion": "1"}}
```

End fight (resolve win/loss if someone is dead; otherwise no-op):

```json
{"query": {"state": "combat", "mode": "combat", "actionEndFight": "1"}}
```

---

## 3. State transitions

### 3.1 How the client moves between states

- **Navigation**: Send the desired **`state`** (and optionally **`mode`**) in the next request. The server responds with that state’s data (or a forced state, e.g. combat when a fight is in progress).
- **Combat start**: From combat enemy selection, send `actionBeginFight`. On success the server performs a **recursive** call with `mode=combat` and returns the **in-fight** response (same request, no second HTTP call). So the **next** response after a valid “begin fight” is already combat in progress.
- **Combat end**: Send `actionEndFight`. The server runs `endFight()`. If player or enemy is dead, response has `mode: "player_death"` or `mode: "player_win"` and `data.result`; otherwise `mode` stays `"combat"` and nothing changes.
- **Daily battle**: Same idea: `actionBeginFight` (with `state=daily-battle`) enters daily combat; `actionEndFight` resolves or keeps `mode=combat`.

### 3.2 Detecting combat state

| Situation | `state` | `mode` | Response content |
|-----------|---------|--------|------------------|
| Combat, enemy selection | `combat` | not `combat` (e.g. `null` or `fight_not_ended`) | `data.info`, `data.currentEnemy` |
| Combat, fight in progress | `combat` | `combat` | `data.info`, `data.session` |
| Combat just ended (win) | `combat` | `player_win` | `data.info`, `data.currentEnemy`, `data.result` (gem, level_up, enemy, etc.) |
| Combat just ended (death) | `combat` | `player_death` | `data.info`, `data.currentEnemy`, `data.result` (lose_xp, lose_gold, enemy, lose_stats) |
| Unfinished fight, other state requested | `combat` | `fight_not_ended` | Forced by server (1014–1016) |

After win/death, `lastFight` is cleared; the next request with `state=combat` will show enemy selection again (no `data.session`).

### 3.3 Detecting win/loss

- **PvE combat**: `mode === "player_win"` or `mode === "player_death"`. Details in `data.result` (rewards/penalties).
- **Daily battle**: Same: `mode === "player_win"` or `mode === "player_death"` with `data.result` (e.g. `lose_xp`, `win_xp`).

### 3.4 Other states

- **main**: Menu; no `data`. Any unrecognized state with a logged-in user becomes `main`.
- **info / equipment / hostel / armor / potion / sword**: Each has `data.info` and state-specific keys (e.g. `data.classes` for info, `data.regenerations` for hostel). Navigate by sending `state=<name>` and optional action (see §4).
- **rank / users**: List states; response includes `players` or `users` (arrays of IDs) and optional pagination params in query.
- **login / profile / report / eula / chat / guestbook**: Present in code but login uses session/redirect; typical API usage focuses on gameplay states above.

---

## 4. Valid actions and examples

Only **one** action is processed per request per state (first matching in the code’s if/elseif chain). Action keys are **presence-based** (any truthy value).

### 4.1 Combat (state = combat)

**Enemy selection** (no active fight: `mode !== 'combat'` or no lastFight):

| Action | In `query` | Effect |
|--------|------------|--------|
| Next enemy | `"actionEnemyNext": "1"` | Select next enemy; response has updated `data.currentEnemy`. |
| Previous enemy | `"actionEnemyPrevious": "1"` | Select previous enemy. |
| Begin fight | `"actionBeginFight": "1"` | If enemy allowed: fight starts; response is **in-fight** (mode=combat, `data.session`). If not allowed: no change, server may set flash (not exposed in API). |

Example — go to combat and start fight:

```json
{"query": {"state": "combat", "actionBeginFight": "1"}}
```

**Active fight** (`mode === 'combat'`, lastFight set):

| Action | In `query` | Effect |
|--------|------------|--------|
| Attack | `"actionFight": "1"` | One turn: player damage, enemy damage, session updated. |
| Use potion | `"actionUsePotion": "1"` | Heal, then enemy attacks; no player attack this turn. |
| End fight | `"actionEndFight": "1"` | Resolve: if someone dead → win/loss and `data.result`; else no-op, mode stays combat. |

Example — attack then end fight:

```json
{"query": {"state": "combat", "mode": "combat", "actionFight": "1"}}
```

```json
{"query": {"state": "combat", "mode": "combat", "actionEndFight": "1"}}
```

### 4.2 Daily battle (state = daily-battle)

| When | Action | In `query` |
|------|--------|------------|
| Not in combat | Start | `"actionBeginFight": "1"` |
| In combat (`mode=combat`) | One exchange | `"actionFight": "1"` |
| In combat | Resolve / no-op | `"actionEndFight": "1"` |

No potion in daily combat (only `actionFight` and `actionEndFight`).

Example — start daily fight:

```json
{"query": {"state": "daily-battle", "actionBeginFight": "1"}}
```

### 4.3 Info (state = info)

| Action | In `query` | Effect |
|--------|------------|--------|
| Choose class | `"actionChooseClasse": "<classId>"` | Sets class if ID is in the class list; else no-op. |
| Add stat point | `"actionAddPoint": "strength"` or `"dexterity"` or `"vitality"` | Adds one point if type valid and points available; else no-op. |

Example:

```json
{"query": {"state": "info", "actionAddPoint": "strength"}}
```

### 4.4 Equipment (state = equipment)

| Action | In `query` |
|--------|------------|
| Equip gem | `"actionEquipGem": "1"` or `"2"` or `"3"` (slot index) |
| Throw gem | `"actionThrowGem": "1"` or `"2"` or `"3"` |

### 4.5 Hostel (state = hostel)

| Action | In `query` |
|--------|------------|
| Regenerate life | `"actionRegeneration": "regeneration0"` … `"regeneration3"` |

### 4.6 Armor / Potion / Sword (state = armor | potion | sword)

| State | Actions (in `query`) |
|-------|----------------------|
| armor | `actionArmorNext`, `actionArmorPrevious`, `actionArmorBuy`, `actionArmorSell` |
| potion | `actionPotionNext`, `actionPotionPrevious`, `actionPotionBuy` |
| sword | `actionSwordNext`, `actionSwordPrevious`, `actionSwordBuy`, `actionSwordSell` |

Example — browse and buy armor:

```json
{"query": {"state": "armor", "actionArmorNext": "1"}}
```

```json
{"query": {"state": "armor", "actionArmorBuy": "1"}}
```

---

## 5. Invalid or irrelevant actions

- **Wrong state**: An action for another state (e.g. `actionFight` while in `state=main`) is **ignored**; the state branch that handles it is not run. Response is normal for the current state (e.g. main with no `data`).
- **Multiple actions**: Only the **first** matching action in the server’s if/elseif chain is executed. Other keys have no effect.
- **actionChooseClasse**: Invalid or unknown class ID → **no-op**; no error in JSON; state unchanged.
- **actionAddPoint**: Invalid type or no points left → **no-op**; no error in JSON.
- **actionEquipGem / actionThrowGem**: Invalid slot or empty slot → **no-op**.
- **actionRegeneration**: Invalid key or insufficient gold → **no-op**; server may set a flash message (not exposed in API).
- **actionBeginFight (combat)**: Enemy not allowed (e.g. above progression or not default when no maxFight) → **no-op**; flash may be set; response stays enemy selection.
- **actionEndFight** when both sides are alive: **no-op**; `mode` stays `combat`; session unchanged.
- **Unknown state**: If the user is logged in and sends an unknown `state` value, the server sets `state` to **`main`** and returns that.

The API does **not** return a dedicated error object for “invalid action”; you infer failure from unchanged state/data or from missing expected changes.

---

## 6. Game-over and end-of-loop semantics

- **No global “game over”**: The code does not define a single terminal game-over state. Outcomes are **per encounter**:
  - **PvE combat**: `mode === "player_win"` or `mode === "player_death"` with `data.result` (rewards or penalties). After that, combat state returns to enemy selection for the next fight.
  - **Daily battle**: Same: `player_win` or `player_death` for that fight; daily stats updated.
- **Turn complete**: One **attack** or **potion** is one turn. After the request, the response contains updated `data.info` (e.g. life) and `data.session` (e.g. `enemy_current_life`, `player_life_lose`, `enemy_life_lose`). The client can call `actionEndFight` after any turn to check for death and resolve; if neither is dead, the response stays in combat and the client can continue with `actionFight` / `actionUsePotion`.
- **Combat “complete”**: Combat is considered over when the response has `mode === "player_win"` or `mode === "player_death"`. Then `data.result` describes the outcome; the next request with `state=combat` is enemy selection again.

---

## 7. Archives (premium)

Archives are **read-only** snapshots. They require a **premium** API key.

### 7.1 List archives

```http
GET /api/darkwood/archives
X-API-Key: <premium-key>
```

**200**: `{ "archives": [ { "id": "<dateId>", "date": "<dateId>" }, ... ] }`  
**403**: `{ "error": "premium_required", "message": "Premium access required" }`

`id` and `date` are the same value (date ID, e.g. `YYYY-MM-DD`). Order is by date descending (DarkwoodArchivesController 32–38).

### 7.2 Get one archive

```http
GET /api/darkwood/archives/<id>
X-API-Key: <premium-key>
```

**200**: JSON body is the **raw stored payload** of that archive (`$archive->getPayload()`). The entity documents that this payload has the **same shape as a `/api/darkwood/state` response** (DarkwoodArchive.php comment). So it is a snapshot of the normalized state at archive time (e.g. `user`, `state`, `mode`, `display`, optional `data`).

**404**: `{ "error": "archive_not_found", "message": "Archive not found" }`  
**403**: Same as above if key is not premium.

### 7.3 How archived state differs from live state

- Archives are **historical snapshots**. They are not connected to the current game session or current user state.
- The payload shape matches the live state response at the time of archiving; entity references in the payload are already normalized (e.g. IDs). Using archived data to “restore” a game session is not implemented in the code; it is read-only.

---

## 8. Caveats and unclear areas

- **User identity**: With only `X-API-Key`, the game user is typically `null` (state `not-logged`) unless your deployment ties the API key to a user or uses another auth mechanism. The guide assumes a logged-in user when describing gameplay states.
- **Redirects**: Login (logout/success) and chat form submit can return an HTTP **redirect** instead of JSON. API clients should handle 3xx and optional Location header if they use those flows.
- **Flash messages**: Validation failures (e.g. not enough gold, invalid enemy) often only set a flash message. The API response does not include the flash bag, so failed actions can look like no-ops.
- **Session**: Combat and daily battle use server-side session (per player). Concurrency or multiple clients for the same user may behave in ways not defined here.
- **Pagination**: For `state=users` and `state=rank`, the response contains only the current page of IDs (`users` or `players`). Total count and page metadata are not returned by the normalization (see docs/darkwood-api-response-structure.md).
- **Entities as IDs**: All entities (user, player, enemy, armor, etc.) are normalized to their **ID** in the JSON. To resolve names or details you need other means (not provided by this API).

---

## 9. File references

| Topic | File / location |
|-------|-----------------|
| GET state | `src/Controller/Api/DarkwoodGetStateController.php` — `__invoke`, `normalizeResult` |
| POST action | `src/Controller/Api/DarkwoodPostActionController.php` — `__invoke` (body + query merge 44–50), `normalizeResult` |
| Play logic | `src/Services/GameService.php` — `play()` 874–804, state/mode/actions |
| Combat | `GameService.php` — fight/endFight 428–449, 451–489; combat branch 1014–1060 |
| Daily battle | `GameService.php` — 1062–1088, fightDaily/endFightDaily 530–578 |
| Beta gate | `src/EventSubscriber/DarkwoodBetaAccessSubscriber.php` |
| Archives list | `src/Controller/Api/DarkwoodArchivesController.php` |
| Archive by id | `src/Controller/Api/DarkwoodArchiveGetController.php` |
| Archive payload shape | `src/Entity/DarkwoodArchive.php` (comment: same shape as state response) |
| Response structure | `docs/darkwood-api-response-structure.md` |
| State machine | `docs/darkwood-gameplay-state-machine.md` |
