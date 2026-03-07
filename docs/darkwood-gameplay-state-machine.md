# Darkwood gameplay state machine (from code)

Reverse-engineered from `GameService::play()` and related code. No rules invented; all references point to actual code.

---

## 1. Gameplay state map

### 1.1 State and mode source

- **State**: `$request->get('state', 'main')` — default is `'main'` (GameService.php:876).
- **Mode**: `$request->get('mode')` — can be `null` or any string; meaning depends on `state`.

### 1.2 All states handled (in evaluation order)

| State | When entered | Data returned | Request params that matter | Next transitions |
|-------|----------------|---------------|----------------------------|------------------|
| **login** | Request has `state=login` | `user`, `state`, `mode`, `display`; if not logging in: `last_username`, `csrf_token`. On logout: redirect. On successful login: redirect to `darkwood_play`. | `mode` (logout / login), `_username`, `_password` | Redirect (logout/success) or stay with login form data |
| **eula** | Request has `state=eula` | `user`, `state`, `mode`, `display` | — | Unclear from code (no transitions) |
| **profile** | Request has `state=profile` | `user` (from `username` or token), `state`, `mode`, `display` | `username` (optional; else current user) | — |
| **report** | Request has `state=report` | Same as profile + `confirm` (true iff `request->get('confirm') === 'true'`) | `username`, `confirm` | — |
| **users** | Request has `state=users` | `user`, `state`, `mode`, `display`, `users` (paginated, 56 per page) | `page`, `sort` (via PaginationDTO) | — |
| **rank** | Request has `state=rank` | `user`, `state`, `mode`, `display`, `players` (paginated, 56 per page; ordering depends on `mode`) | `mode` (rank filter), `page`, `sort` | — |
| **chat** / **guestbook** | Request has `state=chat` or `state=guestbook` | `user`, `state`, `mode`, `display`, `form`, `comments` (paginated, 10 per page). On valid POST form: redirect to `darkwood_play`. | POST body for comment form, `page`, `sort` | Redirect on submit or stay |
| **combat** | (1) Request has `state=combat`, or (2) user has `lastFight` and request had `state !== 'combat'` and `mode !== 'combat'` → then **forced** to `state=combat`, `mode=fight_not_ended` (671–672) | See “Combat state data” below | Many; see §3 | Forced to combat when unfinished fight; combat → win/death or back to enemy choice |
| **daily-battle** | Request has `state=daily-battle` | See “Daily-battle data” below | `actionBeginFight`, `actionFight`, `actionEndFight` | combat mode → player_win / player_death or stay combat |
| **info** | Request has `state=info` | `data.info`, `data.classes` | `actionChooseClasse`, `actionAddPoint` | — |
| **equipment** | Request has `state=equipment` | `data.info` | `actionEquipGem`, `actionThrowGem` | — |
| **hostel** | Request has `state=hostel` | `data.info`, `data.regenerations` | `actionRegeneration` | — |
| **armor** | Request has `state=armor` | `data.info`, `data.armor`, `data.currentArmor` | `actionArmorNext`, `actionArmorPrevious`, `actionArmorBuy`, `actionArmorSell` | — |
| **potion** | Request has `state=potion` | `data.info`, `data.potion`, `data.currentPotion` | `actionPotionNext`, `actionPotionPrevious`, `actionPotionBuy` | — |
| **sword** | Request has `state=sword` | `data.info`, `data.sword`, `data.currentSword` | `actionSwordNext`, `actionSwordPrevious`, `actionSwordBuy`, `actionSwordSell` | — |
| **main** | Request has `state=main` or any other **unrecognized** state (when user is logged in) | `user`, `state` (set to `'main'`), `mode`, `display` (798–799) | — | — |
| **not-logged** | `$user` is not an `User` instance | `user`, `state` (set to `'not-logged'`), `mode`, `display` (801–802) | — | — |

### 1.3 Combat state data (state = combat)

- **When `mode === 'combat'` and `player->getLastFight()`** (active PvE fight):
  - **Returned**: `parameters['data']['info']` = `getInfo($user)`, `parameters['data']['session']` = `getSession($user)` (1032–1033).
  - **Session** (from `getSession`, 418–426): `player_life_lose`, `enemy_current_life`, `enemy_life_lose`; initialized from `lastFight` enemy life if not already an array.
  - **Actions**: `actionFight`, `actionUsePotion`, `actionEndFight` (see §3). After an action, same data is returned (updated).

- **When in combat but not in active fight** (e.g. `mode !== 'combat'` or no `lastFight` — enemy selection):
  - **Returned**: `parameters['data']['info']`, `parameters['data']['currentEnemy']` = `getEnemyInfo(currentEnemy ?? default)` (1058–1059).
  - **Actions**: `actionEnemyNext`, `actionEnemyPrevious`, `actionBeginFight`. On valid `actionBeginFight`, `setLastFight` is called, `mode` is set to `'combat'` in request attributes, and `play()` is **recursively** called (1051–1054); the recursive call then returns combat data with `mode === 'combat'` and session.

### 1.4 Daily-battle state data (state = daily-battle)

- **When `mode === 'combat'`**: `data.session` = `getSessionDaily($user)`; actions `actionFight`, `actionEndFight` (1064–1074).
- **Else**: `data.dailyBattles` = `getDailyBattles()`; action `actionBeginFight` (1077–1083).
- **Always**: `data.info`, `data.dailyEnemyInfo` (1086–1087).

---

## 2. Mode values and interaction with state

- **Where set by code** (not only by request):
  - **combat**: `state=combat` and active fight: `mode` is `'combat'` when entering from `actionBeginFight` (recursive call with `request->attributes->set('mode', 'combat')`). When user has unfinished fight but requested another state: forced to `state=combat`, `mode=fight_not_ended` (671–672).
  - **player_win** / **player_death**: set from `endFight()` or `endFightDaily()` return value (1025–1029, 1068–1071).

- **Meaningful mode values (from code)**:
  - **login**: `logout`, `login` (582–616).
  - **rank**: `by_class_human`, `by_class_lucky_lucke`, `by_class_panoramix`, `by_class_popeye`, `daily_fight_by_defeats`, `daily_fight_by_victories`, or `null` (PlayerRepository 30–55; GameService 979).
  - **combat**: `fight_not_ended` (forced redirect), `combat` (active fight), `player_win`, `player_death`.
  - **daily-battle**: `combat` (active daily fight), `player_win`, `player_death`.

- **Interaction**: State selects the screen/flow; mode refines it (e.g. rank sort, combat phase, login sub-action). In combat, mode drives whether the handler runs the “enemy selection” branch or the “turn-by-turn fight” branch.

---

## 3. Request parameters and effects

Actions are detected with `$request->get('actionX')` — any present value is truthy; only one action is processed per branch (first matching in the if/elseif chain).

### 3.1 Combat (state = combat)

| Parameter | Meaningful when | Method | Effect on returned state/mode |
|-----------|------------------|--------|-------------------------------|
| **actionFight** | `state=combat`, `mode=combat`, `lastFight` set | `fight($user, 'fight')` (1019–1020) | One attack turn; player and enemy damage applied; session updated; `state`/`mode` unchanged; next response still combat with updated `data.info` and `data.session`. |
| **actionUsePotion** | Same | `fight($user, 'potion')` (1021–1022) | Player heals (potion applied), then enemy attacks; no player attack this turn; session updated; same return shape. |
| **actionEndFight** | Same | `endFight($user)` (1023–1030) | If player life ≤ 0: `mode=player_death`, `data.result` with lose_xp, lose_gold, enemy, lose_stats; `lastFight` and session cleared. If enemy life ≤ 0: `mode=player_win`, `data.result` with gem, level_up, enemy (and optional gem_item); XP/gold/gem applied; `lastFight` and session cleared. Else: `mode` stays `combat`, session unchanged (no transition). |
| **actionEnemyNext** | `state=combat`, not in active fight (no `lastFight` or `mode !== 'combat'`) | `nextEnemy($user)` (1036–1037) | `currentEnemy` advances; returns `data.info`, `data.currentEnemy`. |
| **actionEnemyPrevious** | Same | `previousEnemy($user)` (1038–1039) | `currentEnemy` goes back; same return. |
| **actionBeginFight** | Same | Validation then `setLastFight($user)` + recursive `play()` with `mode=combat` (1040–1055) | If invalid enemy: flash warning, no state change, same data. If valid: fight starts, recursive call returns combat with `mode=combat` and session. |

### 3.2 Daily-battle (state = daily-battle)

| Parameter | Meaningful when | Method | Effect |
|-----------|------------------|--------|--------|
| **actionFight** | `state=daily-battle`, `mode=combat` | `fightDaily($user)` (1064–1065) | One exchange; session updated; same state/mode. |
| **actionEndFight** | Same | `endFightDaily($user)` (1066–1071) | If player life ≤ 0: `mode=player_death`, result; daily stats updated. If enemy life ≤ 0: `mode=player_win`, result; daily stats updated. Else: `mode` stays `combat`. |
| **actionBeginFight** | `state=daily-battle`, not `mode=combat` | Sets `mode=combat`, recursive `play()` (1077–1080) | Enters daily combat; next response has `data.session` and combat actions. |

### 3.3 Info (state = info)

| Parameter | Meaningful when | Method | Effect |
|-----------|------------------|--------|--------|
| **actionChooseClasse** | `state=info` | `chooseClasse($user, $request->get('actionChooseClasse'))` (1090–1091) | Classe set only if ID matches a class in list (295–301); otherwise no-op. |
| **actionAddPoint** | `state=info` | `addPoint($user, $request->get('actionAddPoint'))` (1092–1093) | Point added only if type in `['strength','dexterity','vitality']` and `points['diff'] > 0` (310–314); else no-op. |

### 3.4 Equipment / hostel / armor / potion / sword

| State | Parameters | Methods |
|-------|-------------|--------|
| **equipment** | actionEquipGem, actionThrowGem | `equipGem($user, index)`, `throwGem($user, index)` — index 1–3; equip only if slot has gem (339–354, 356–369). |
| **hostel** | actionRegeneration | `regen($user, key)` — key must be in getRegenerations (e.g. regeneration0…3); fails silently if not or not enough gold (369–392). |
| **armor** | actionArmorNext, actionArmorPrevious, actionArmorBuy, actionArmorSell | next/previous/buy/sell (393–416, 418–441); buy/sell can add flash on validation failure. |
| **potion** | actionPotionNext, actionPotionPrevious, actionPotionBuy | next/previous/buy (402–424, 438–458). |
| **sword** | actionSwordNext, actionSwordPrevious, actionSwordBuy, actionSwordSell | next/previous/buy/sell (461–523); buy clears all gem slots. |

---

## 4. Internal flow of a single turn (one API call)

1. **Input**: Request (query or body merged into query by `DarkwoodPostActionController`), optional `$user`, optional `$display`. `state` = `request->get('state', 'main')`, `mode` = `request->get('mode')` (876).

2. **Display**: If `display` not in `['web','iphone','ipad','mac']`, set to `'web'` (578–580).

3. **State dispatch**: A single top-level chain of `if ($parameters['state'] === '...')` is evaluated in order. First match wins; no later state block runs.

4. **Forced combat redirect** (only when `$user` is User): If `player->getLastFight()` and `state !== 'combat'` and `mode !== 'combat'`, then `state = 'combat'`, `mode = 'fight_not_ended'` (671–672). Then the `elseif ($parameters['state'] === 'combat')` block runs.

5. **Passive vs action**:
   - **Passive**: If the matched state block does not run an action (no matching `request->get('action...')`), it only builds and returns `$parameters` (with optional pagination, form, etc.).
   - **Action**: If an action parameter is present, the corresponding service method is called (e.g. `fight`, `endFight`, `nextEnemy`). That method may change DB and session. Then the same state block still builds and returns `$parameters` (with updated `data`).

6. **Combat progression** (state = combat, mode = combat):
   - **actionFight**: `fight($user, 'fight')` — player damage roll, enemy damage roll, hit luck check; session and player life updated (428–449).
   - **actionUsePotion**: `fight($user, 'potion')` — heal then enemy attack; no player damage this turn (430–439, 448).
   - **actionEndFight**: `endFight($user)` — if player dead: apply death penalties, clear lastFight/session, return `mode=player_death`; if enemy dead: apply rewards, gem chance, clear lastFight/session, return `mode=player_win`; else return `mode=null` and play() keeps `mode=combat` (1023–1030, 446–489).

7. **Recursive re-entry**: `actionBeginFight` (combat or daily-battle) and successful validation set `mode=combat` on the request and `return $this->play($request, null, $user)` (1052–1054, 1078–1080). The second call then runs with the same request (and attributes) and returns the “in combat” response.

8. **Default**: If user is logged in and no state matched, `state` is set to `'main'` (798). If user is not logged in, `state` is set to `'not-logged'` (801).

9. **Output**: `$parameters` array (or a `RedirectResponse` for login logout/success and sometimes chat POST). API controllers normalize this to JSON (entities reduced to IDs or strings where applicable).

---

## 5. Invalid or ignored actions

- **Wrong state**: An action parameter for another state (e.g. `actionFight` when `state=main`) is never read; it is ignored (no branch matches that state).

- **Multiple actions in one state**: Only the first matching action in the if/elseif chain runs (e.g. if both `actionFight` and `actionUsePotion` are set, only `actionFight` runs) (1019–1030 and similar).

- **actionChooseClasse**: Invalid or non-existent class ID: `chooseClasse` returns early (300–301); no error in response, state unchanged.

- **actionAddPoint**: Invalid type or no points left: `addPoint` returns early (313–314); no-op.

- **actionEquipGem / actionThrowGem**: Invalid index or empty slot (equip): method does nothing for that slot (339–354, 356–369).

- **actionRegeneration**: Invalid key or insufficient gold: `regen` returns early or adds flash (371–384); API may not expose flash.

- **actionBeginFight (combat)**: Enemy not allowed (e.g. above `maxFight` or not default when no maxFight): flash warning, no `setLastFight`, no recursion (1044–1049); response stays enemy-selection combat.

- **actionEndFight** when neither side is dead: `endFight`/`endFightDaily` does not set win/lose; `mode` stays `combat`; session unchanged (1027–1029, 1069–1071).

- **Daily-battle**: No `actionUsePotion`; potion action is not implemented in daily combat (only `actionFight` and `actionEndFight`).

- **Silent fallback**: If user is logged in and state is any string that is not one of the explicit states (e.g. typo), code falls through to `else { $parameters['state'] = 'main'; }` (798–799) — response looks like main menu with `state=main`.

---

## 6. File references (selected)

- **GameService::play()**: 574–804 (state/mode/actions and transitions).
- **State/mode init**: 876.
- **Forced combat**: 1014–1016.
- **Combat actions and data**: 1017–1060.
- **Daily-battle**: 1061–1087.
- **Info / equipment / hostel / armor / potion / sword**: 1088–797.
- **Main / not-logged**: 798–802.
- **fight()**: 428–449.
- **endFight()**: 451–489.
- **getSession()**: 418–426.
- **setLastFight()**: 416–417 (only sets if null).
- **PlayerRepository::findActiveQuery($mode)**: 30–55 (rank modes).
- **DarkwoodPostActionController**: 44–50 (query params from JSON body merged into request).
- **DarkwoodGetStateController**: 30–31 (calls `play($request, null, $user)`).

---

## 7. Ambiguities

- **eula**: No transitions or side effects; intent (e.g. “must accept before play”) not visible in this flow.
- **Exact action parameter semantics**: Code only checks presence (`$request->get('actionX')`); value (e.g. `"1"` vs `"true"`) is not documented; both would trigger the action.
- **Redirect vs JSON**: Login and chat can return `RedirectResponse`; API controllers would need to handle that (e.g. 33–34 in DarkwoodGetStateController) — behavior when client expects JSON is unclear from code.
- **Flash messages**: Many failures only call `addFlash()`; API normalization does not show flash bag, so clients may see success-like state with no indication of failure.
- **Session storage**: Combat and daily combat depend on server session (`getSession` / `getSessionDaily`); session key is per player ID. Behavior with multiple tabs or no cookies is unclear from code.
- **actionEndFight when fight not over**: Code allows calling “end fight” even when both are alive; it’s a no-op (mode stays combat). Whether this is “check for death” vs “forfeit” is not distinguished in code.
