# Darkwood API response structure

Inferred from `DarkwoodGetStateController`, `DarkwoodPostActionController`, and `GameService::play()`. No fields invented.

**Endpoints**: `GET /api/darkwood/state`, `POST /api/darkwood/action`. Both call `GameService::play($request, null, $user)` and then `normalizeResult($result)` before returning JSON (or a redirect in some login cases).

---

## 1. Top-level response shape

### 1.1 Successful JSON responses

The response is the normalized `$parameters` array returned by `GameService::play()`. Initial keys set in every run (GameService.php:876):

| Key      | Type    | Always present | Description |
|----------|---------|----------------|-------------|
| `user`   | int\|null | Yes          | Current user ID (from token or request); `null` when not logged in. After normalization, `User` entity → `getId()` (DarkwoodGetStateController 82–84). |
| `state`  | string  | Yes            | Game state; default `'main'` (876). |
| `mode`   | string\|null | Yes     | Sub-mode; from request or set by code (e.g. combat, rank filter). |
| `display`| string  | Yes            | `'web'`, `'iphone'`, `'ipad'`, or `'mac'`; default `'web'` (877–879). |

State- or branch-specific top-level keys (only when that branch runs):

| Key | Type | States / conditions |
|-----|------|------------------------|
| `last_username` | string | `state=login` (form display), when not redirecting (922–923, 902–903). |
| `csrf_token` | string | Same as above (922–923, 902–903). |
| `confirm` | boolean | `state=report`; true iff `request->get('confirm') === 'true'` (964). |
| `users` | array | `state=users`; normalized paginator → list of user IDs (973). |
| `players` | array | `state=rank`; normalized paginator → list of player IDs (982). |
| `form` | (nested structure) | `state=chat` or `state=guestbook`; `form->createView()` normalized (1006). |
| `comments` | array | `state=chat` or `state=guestbook`; normalized paginator → list of comment IDs (1007). |
| `data` | object | All logged-in gameplay states that set `parameters['data']`: combat, daily-battle, info, equipment, hostel, armor, potion, sword. Contents are state-specific (see §2 and §5). |

### 1.2 Non-JSON responses

- **Redirect**: When `play()` returns a `RedirectResponse`, the controller returns it as-is (DarkwoodGetStateController 33–34, DarkwoodPostActionController 55–56). So the client gets an HTTP redirect (e.g. 302), not JSON. This happens for:
  - `state=login` + `mode=logout` (886).
  - `state=login` + successful login (except the special “apple” case that returns `$parameters`) (911).
  - `state=chat` or `state=guestbook` + valid POST form submit (654).
- **POST /api/darkwood/action** only: invalid request body JSON → `400` with body `{ "error": "invalid_json", "message": "Request body must be valid JSON" }` (38–41).

---

## 2. Nested structures

### 2.1 `data.info` (player info)

From `GameService::getInfo($user)` (172–230). Normalization applies to every value; entities become IDs.

| Key | Type (after normalization) | Description |
|-----|----------------------------|-------------|
| `user` | int | User ID. |
| `player` | int | Player ID. |
| `level` | int\|null | LevelUp ID; can be `null` when fallback LevelUp is used (not persisted) (174–179). |
| `damage` | object | `min`, `max` (int). |
| `swordDamage` | int | |
| `equipmentDamage` | int | |
| `hitLuck` | int | |
| `armor` | int | |
| `armorDefence` | int | |
| `points` | object | `total`, `max`, `diff` (int). |
| `life` | object | `min`, `max`, `diff` (int). |

Present in: `data` for combat (1033), daily-battle (1087), info (1096), equipment (1105), hostel (1111), armor (1124), potion (1136), sword (1150); and inside each `dailyBattles[]` item (771) and as `data.dailyEnemyInfo` (1088).

### 2.2 `data.session` (PvE combat)

From `getSession($user)` (418–426). Plain array; no entities.

- **When in active fight**: `player_life_lose` (int), `enemy_current_life` (int), `enemy_life_lose` (int).
- **When no session**: can be `null` (session key not set or no enemy).

Set in combat when `mode === 'combat'` and `player->getLastFight()` (1034).

### 2.3 `data.session` (daily battle)

From `getSessionDaily($user)` (574–584). Plain array.

- `player_current_life` (int), `player_life_lose` (int), `enemy_current_life` (int), `enemy_life_lose` (int).
- Can be `null` if not yet initialized.

Set in daily-battle when `mode === 'combat'` (1076).

### 2.4 `data.currentEnemy` (enemy selection in combat)

From `getEnemyInfo($enemy)` (249–252). After normalization:

| Key | Type | Description |
|-----|------|-------------|
| `enemy` | int | Enemy ID. |
| `next` | int\|null | Next enemy ID or null. |
| `previous` | int\|null | Previous enemy ID or null. |

Set in combat when not in active fight (1060).

### 2.5 `data.result` (end of PvE fight)

From `endFight($user)` return value (451–489), only when `mode === 'player_win'` or `mode === 'player_death'` (1026–1027). Normalization turns entities into IDs.

**On player death** (`result` from 667–681):

| Key | Type | Description |
|-----|------|-------------|
| `lose_xp` | int | |
| `lose_gold` | int | |
| `enemy` | int | Enemy ID. |
| `lose_stats` | float | `GameService::DEATH_LOSE_STATS`. |

**On player win** (`result` from 691–744):

| Key | Type | Description |
|-----|------|-------------|
| `gem` | string | `'not_found'`, `'found'`, or `'no_place'`. |
| `level_up` | boolean | |
| `enemy` | int | Enemy ID. |
| `gem_item` | int | Gem ID; present when `gem === 'found'` or `gem === 'no_place'` (733, 474). |

### 2.6 `data.result` (end of daily battle)

From `endFightDaily($user)` (546–578). No entities in result; only:

| Key | Type | Description |
|-----|------|-------------|
| `lose_xp` | int | |
| `win_xp` | int | |

Set when `mode === 'player_win'` or `mode === 'player_death'` (1069–1070).

### 2.7 `data.classes`

From `getClasses()` (254–256). After normalization:

| Key | Type | Description |
|-----|------|-------------|
| `default` | int | Classe ID. |
| `list` | int[] | Classe IDs (order from repository). |

Present only when `state=info` (1097).

### 2.8 `data.regenerations`

From `getRegenerations($user)` (258–294). Plain object; keys `regeneration0` … `regeneration3`; each value:

| Key | Type | Description |
|-----|------|-------------|
| `life` | int | |
| `price` | int | |

Present only when `state=hostel` (1112).

### 2.9 `data.armor`, `data.currentArmor` (and potion/sword analogues)

From `getArmorInfo()` (233–236), `getPotionInfo()` (238–241), `getSwordInfo()` (243–246). After normalization:

| Key | Type | Description |
|-----|------|-------------|
| `armor` / `potion` / `sword` | int | Entity ID. |
| `sellPrice` | int | Only for armor and sword. |
| `next` | int\|null | |
| `previous` | int\|null | |

- **armor**: `data.armor` (equipped), `data.currentArmor` (browsing); state=armor (1125–1126).
- **potion**: `data.potion`, `data.currentPotion`; state=potion (1137–1138).
- **sword**: `data.sword`, `data.currentSword`; state=sword (1151–1152).

### 2.10 `data.dailyBattles`

From `getDailyBattles()` (769–775). Array of objects; each element:

| Key | Type | Description |
|-----|------|-------------|
| `info` | object | Same shape as `data.info` (§2.1); for the battle’s player’s user. |
| `dailyBattle` | int | DailyBattle ID. |

Present when `state=daily-battle` and not `mode=combat` (1084).

### 2.11 `data.dailyEnemyInfo`

Same structure as `data.info`; for the daily opponent user (1088). Present when `state=daily-battle`.

### 2.12 Ranking and user lists (`users`, `players`)

- **`users`** (state=users): From `$this->paginator->paginate($query, …)` (972). The paginator is `Traversable`; normalization iterates it and normalizes each item (DarkwoodGetStateController 73–79). Each item is a `User` → `getId()`. So **`users` is an array of user IDs** (integers), in page order. Pagination metadata (total, current page) is **not** added to the response; only the current page’s item IDs.
- **`players`** (state=rank): Same mechanism; each item is a `Player` → **array of player IDs** (982).

### 2.13 Comments and form (`comments`, `form`)

- **`comments`**: Paginated comment query; normalized like above → **array of comment IDs** (Comment/CommentPage have `getId()`).
- **`form`**: `$form->createView()` (1006). `FormView` is `Traversable`; normalized recursively. Children are normalized (getId → id, __toString → string, etc.). **Exact JSON shape is frontend-oriented and not guaranteed** from static analysis; it is a tree of form view data (field names and normalized values).

---

## 3. Serialization (normalization) behavior

Logic: `normalizeResult()` in both API controllers (DarkwoodGetStateController 49–90, DarkwoodPostActionController 70–111). Order of checks:

1. **`null` or scalar** → returned as-is (51–53).
2. **`DateTimeInterface`** → `$value->format(DATE_ATOM)` (55–57).
3. **`BackedEnum`** → `$value->value` (59–61).
4. **Array** → recursive normalize on each value; keys preserved (64–69).
5. **`Traversable`** (non-array) → iterate, normalize each item, **numeric array** (72–79). So paginator → list of normalized items; no pagination meta.
6. **Object with `getId()`** → `$value->getId()` (82–84). All game entities (User, Player, LevelUp, Armor, Sword, Potion, Enemy, Classe, Gem, DailyBattle, Comment, etc.) have `getId()` → **int or null**.
7. **Object with `__toString()`** → `(string) $value` (86–88).
8. **Otherwise** → `null` (90).

Consequences:

- **Entities** (User, Player, Enemy, Armor, Sword, Potion, Classe, Gem, LevelUp, DailyBattle, Comment): in the JSON they appear as **IDs** (or null), never as nested objects.
- **getInfo()**: `user`, `player`, `level` in the returned array become IDs; `damage`, `points`, `life` stay as scalar/array.
- **getEnemyInfo/getArmorInfo/getPotionInfo/getSwordInfo**: `enemy`/`armor`/`potion`/`sword` and `next`/`previous` become IDs.
- **getClasses()**: `default` and each element of `list` become IDs.
- **endFight result**: `enemy`, `gem_item` become IDs.
- **Paginator**: Only the list of IDs for the current page; no `totalCount`, `page`, etc.

---

## 4. Field glossary (important fields)

| Field | Type (normalized) | Where | Meaning |
|-------|-------------------|-------|---------|
| `user` | int\|null | Top-level | Current user ID; null if not logged in. |
| `state` | string | Top-level | Current game screen/flow. |
| `mode` | string\|null | Top-level | Sub-mode (e.g. combat phase, rank filter, login action). |
| `display` | string | Top-level | Client type: web, iphone, ipad, mac. |
| `data` | object | Top-level | State-specific payload; only for logged-in gameplay states. |
| `data.info` | object | In `data` | Player stats and derived values (damage, life, points, etc.); entities inside are IDs. |
| `data.session` | object\|null | In `data` | Combat or daily-battle session (life deltas, current lives). |
| `data.currentEnemy` | object | In `data` (combat) | Current enemy + next/previous IDs. |
| `data.result` | object | In `data` (combat/daily) | Present when fight just ended (player_win/player_death); win/loss rewards and penalties. |
| `data.classes` | object | In `data` (info) | default + list of class IDs. |
| `data.regenerations` | object | In `data` (hostel) | regeneration0…3 with life/price. |
| `data.armor` / `currentArmor` | object | In `data` (armor) | Equipped vs browsing armor (id, sellPrice, next, previous). |
| `data.potion` / `currentPotion` | object | In `data` (potion) | Same idea for potion. |
| `data.sword` / `currentSword` | object | In `data` (sword) | Same for sword. |
| `data.dailyBattles` | array | In `data` (daily-battle) | List of { info, dailyBattle id }. |
| `data.dailyEnemyInfo` | object | In `data` (daily-battle) | Same shape as info for daily opponent. |
| `users` | int[] | Top-level (state=users) | Current page of user IDs. |
| `players` | int[] | Top-level (state=rank) | Current page of player IDs. |
| `comments` | int[] | Top-level (chat/guestbook) | Current page of comment IDs. |
| `form` | (nested) | Top-level (chat/guestbook) | Form view tree; structure not guaranteed for API. |
| `last_username` | string | Top-level (login) | For login form display. |
| `csrf_token` | string | Top-level (login) | CSRF token. |
| `confirm` | boolean | Top-level (report) | Report confirmation flag. |

---

## 5. State-specific schemas (concise)

- **main** (default when logged in, unknown state): `user`, `state` (`'main'`), `mode`, `display`. No `data`.
- **not-logged**: `user` (null), `state` (`'not-logged'`), `mode`, `display`. No `data`.
- **login**: `user`, `state`, `mode`, `display`; plus `last_username`, `csrf_token` when showing form. If redirect (logout/success), response is not JSON.
- **eula**: `user`, `state`, `mode`, `display`.
- **profile**: `user` (resolved), `state`, `mode`, `display`.
- **report**: `user`, `state`, `mode`, `display`, `confirm`.
- **users**: `user`, `state`, `mode`, `display`, `users` (int[]).
- **rank**: `user`, `state`, `mode`, `display`, `players` (int[]).
- **chat** / **guestbook**: `user`, `state`, `mode`, `display`, `form`, `comments` (int[]). On valid POST submit, redirect (no JSON).
- **combat**: `user`, `state` (`'combat'`), `mode`, `display`, `data`.
  - **data**: `info` always; `session` when `mode === 'combat'` (active fight); `currentEnemy` when not in active fight; `result` when `mode === 'player_win'` or `player_death'` (after endFight).
- **daily-battle**: `user`, `state`, `mode`, `display`, `data`. **data**: `info`, `dailyEnemyInfo` always; `session` when `mode === 'combat'`; `dailyBattles` when not in combat; `result` when mode is player_win/player_death.
- **info**: `user`, `state`, `mode`, `display`, `data`: `info`, `classes`.
- **equipment**: `user`, `state`, `mode`, `display`, `data`: `info`.
- **hostel**: `user`, `state`, `mode`, `display`, `data`: `info`, `regenerations`.
- **armor**: `user`, `state`, `mode`, `display`, `data`: `info`, `armor`, `currentArmor`.
- **potion**: `user`, `state`, `mode`, `display`, `data`: `info`, `potion`, `currentPotion`.
- **sword**: `user`, `state`, `mode`, `display`, `data`: `info`, `sword`, `currentSword`.

---

## 6. Uncertain or runtime-dependent fields

- **`level` inside `data.info`**: Can be `null` when `levelUpRepository->findByXp()` returns null and the fallback LevelUp is used (not persisted, so `getId()` is null) (174–179).
- **`data.session`**: Can be `null` in combat if session was never initialized (e.g. no enemy) (418–426).
- **Paginator iteration**: Knp Paginator is Traversable; normalization produces only the current page’s items (IDs). Total count, page number, and page size are **not** in the response; exact number of IDs depends on per-page limit (56 for users/players, 10 for comments) and current page.
- **`form`**: Exact keys and nesting come from Symfony FormView; not designed for API. May contain field names, CSRF token, and normalized child values; can change with form type or Symfony version.
- **Redirect on login**: API client will receive HTTP redirect (e.g. 302) for logout and successful login; body is not the usual JSON state.
- **POST body for actions**: For `POST /api/darkwood/action`, `query` is merged from JSON body (44–50); other body keys are not documented here and may or may not affect response shape.

---

## 7. File references

- **DarkwoodGetStateController**: `__invoke` 28–40, `normalizeResult` 49–90.
- **DarkwoodPostActionController**: `__invoke` 29–61 (incl. JSON error 38–41), `normalizeResult` 70–111.
- **GameService::play()**: 874–804 (initial parameters 876, all branches that set `parameters`).
- **GameService::getInfo()**: 172–230.
- **GameService::getSession / getSessionDaily**: 418–426, 574–584.
- **GameService::getEnemyInfo, getArmorInfo, getPotionInfo, getSwordInfo**: 233–252.
- **GameService::getClasses, getRegenerations, getDailyBattles**: 254–294, 769–775.
- **GameService::endFight / endFightDaily**: 451–489, 546–578.
