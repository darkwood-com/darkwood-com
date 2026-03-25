# Hello CV data (normalized dataset)

## Source

- **File:** `/Users/math/pCloud Drive/MathieuData/Darkwood/tasks/#90c62f_cv/mathieu-ledru.docx`
- **Extraction:** Plain text from `word/document.xml` inside the DOCX (Office Open XML).

## Where it lives in code

- **Service:** `App\Service\HelloCvRepository` — single source of truth for API + MCP.
- **Shape:** `App\ApiResource\HelloCv*` DTOs (readonly) returned by providers and normalized for MCP processors.

## Normalization conventions

- **Dates:** `YYYY-MM` for month precision; `endDate: null` when the role is ongoing (EEAS block in the CV).
- **Location:** Current engagement lists Brussels; profile `location` reflects that.
- **Stacks / highlights:** Flat `string[]`; short, factual strings; no duplicated marketing copy between `description` and `highlights`.
- **Links:** `[{ "label": string, "url": string }, ...]` for stable machine consumption.
- **Systems:** `Flow`, `Uniflow`, and `Darkwood` appear explicitly in the CV; each gets an `id`, short `description`, and repository links where given.
- **Search (`hello_search_cv`):** Case-insensitive substring match over company, role, description, stack, highlights, project name/description/tags, and skill name/category.

## Assumptions / ambiguities

- **EEAS end date:** The summary table shows “Juin 2025 – Mars 2026” while another line shows “Juin 2025 – Mai 2026”. The structured data uses **ongoing** (`endDate: null`) with **start `2025-06`**, consistent with a current engagement; no fabricated end date.
- **Twenga end month:** “Mai 2025” in the CV → `2025-05`.
- **Gamestream / Mediatech date casing:** Normalized to ISO month tokens (`2024-04`, `2022-12`, etc.).
- **ANFR / Kylotonn internships:** Included as short experience rows for completeness; labeled clearly as internship-era work.
- **Skills list:** Curated subset of technologies and practices named in the CV (not an exhaustive keyword dump of every brand string).

## Public HTTP API (flat Hello-prefixed paths under `/api`)

- `GET /api/hello_profile` — operation name `api_hello_profile_get`
- `GET /api/hello_experiences` — `api_hello_experiences_get_collection`
- `GET /api/hello_projects` — `api_hello_projects_get_collection`
- `GET /api/hello_skills` — `api_hello_skills_get_collection`

Host: API platform routes use `%api_host%` (e.g. `api.darkwood.localhost` in dev).

## Example JSON (HTTP)

`GET /api/hello_profile`:

```json
{
  "name": "Mathieu Ledru",
  "role": "Senior backend / fullstack engineer",
  "summary": "Ten+ years delivering PHP/Symfony backends...",
  "location": "Brussels, Belgium",
  "links": [{ "label": "Freelance (Hello)", "url": "https://hello.darkwood.fr" }],
  "systems": [
    {
      "id": "flow",
      "name": "Flow",
      "description": "PHP orchestration: async composition...",
      "links": [{ "label": "GitHub", "url": "https://github.com/darkwood-fr/flow" }]
    }
  ]
}
```

## MCP tools (Hello-prefixed names)

- **`hello_get_profile`:** Same normalized profile object as HTTP (JSON-serialized, `cv:read` groups).
- **`hello_list_experiences`:** `{ "experiences": [ ... ] }`
- **`hello_get_experience`:** `{ "matched": true, "experience": { ... } }` or `{ "matched": false, "experience": null }`
- **`hello_list_projects`:** `{ "projects": [ ... ] }`
- **`hello_search_cv`:** `{ "query": "...", "experiences": [], "projects": [], "skills": [] }`
- **`hello_list_skills`:** `{ "skills": [ ... ] }` (sorted by category, then name)
