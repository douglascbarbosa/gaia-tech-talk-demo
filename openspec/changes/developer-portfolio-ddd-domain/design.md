## Context

The application is **Laravel + Vue** with **Fortify** for authentication. Today the authenticated record is `App\Models\User` backed by the `users` table (with two-factor columns). Sessions reference `user_id`. The product domain is shifting to a **Developer Portfolio Portal**, so the core identity should be expressed as **Developer** in code and schema, with room to grow into projects and experience as separate aggregates later.

## Goals / Non-Goals

**Goals:**

- Introduce a **domain layer** for the Developer aggregate: entity, value objects, and repository **interface** owned by the domain.
- Rename persistence and application concepts from **User** to **Developer** where they represent the portfolio owner (ubiquitous language), while keeping Laravel auth integration working.
- Persist `github_profile`, **availability status** (`open_for_new_jobs` | `working`), and **Address** (street, city, postal code, country) with validation rules agreed in application/domain boundaries.
- Provide **DeveloperRepository** plus an **application service** (use-case layer) implementing **CRUD** for developers as orchestration over the repository and domain constructors/factories.

**Non-Goals:**

- Building portfolio **projects** or **experience** aggregates (only Developer profile and structure in this change).
- API versioning or public developer APIs beyond what existing app routes need.
- Event sourcing or full CQRS (simple service + repository is sufficient).

## Decisions

1. **Bounded context and package layout (Laravel)**  
   - **Choice**: Place domain code under `app/Domain/Developer/` (entity, value objects, `DeveloperRepositoryInterface`), application services under `app/Application/Developer/` (or `app/Services/Developer/` if the repo already favors `App\Services` — prefer `Application` for DDD clarity), and infrastructure implementations under `app/Infrastructure/Persistence/Developer/` (Eloquent repository, mapping).  
   - **Rationale**: Keeps framework code at the edges and makes ubiquitous language visible in namespaces.  
   - **Alternatives**: Keep everything in `app/Models` only — rejected because it collapses domain and persistence; use `src/Domain` — rejected to avoid fighting Laravel’s default PSR-4 `app/` root without stronger convention.

2. **Table and model rename**  
   - **Choice**: Migrate `users` → `developers` with equivalent columns; rename `user_id` foreign keys that refer to this identity (for example `sessions.user_id` → `developer_id`, or keep Laravel session driver compatibility by documenting a deliberate exception — **prefer** renaming to `developer_id` and updating `config/session.php` / migration if Laravel allows custom column name for session user reference; if framework constraints are too high, document retaining `user_id` column name only for sessions as a short-term compromise).  
   - **Rationale**: Schema should match ubiquitous language.  
   - **Alternatives**: Keep `users` table name — rejected for explicit ubiquitous language requirement.

3. **Address persistence**  
   - **Choice**: Store address as **four nullable columns** on `developers` (`address_street`, `address_city`, `address_postal_code`, `address_country`) mapped to an `Address` value object in the domain, with the VO enforcing “all present or all absent” if that invariant is desired.  
   - **Rationale**: Simple querying and migrations; VO still encapsulates behavior.  
   - **Alternatives**: Single `json` column — acceptable; rejected here to keep constraints and indexing straightforward for a first iteration.

4. **Status representation**  
   - **Choice**: Database `string` or native `ENUM` (MySQL/Postgres dependent) with canonical values `open_for_new_jobs` and `working`; domain enum or small value object mirroring these.  
   - **Rationale**: Matches stated business language while staying grep-friendly.

5. **`github_profile`**  
   - **Choice**: Store as `string` (URL or `@handle`); validate at application boundary (format rules in tasks/spec scenarios).  
   - **Rationale**: Flexible display and linking without over-modeling GitHub’s API.

6. **Auth integration**  
   - **Choice**: `Developer` extends `Authenticatable` (or composition with a dedicated auth adapter) so Fortify continues to resolve the same guard provider model class after `config/auth.php` update.  
   - **Rationale**: Minimize custom guard code while satisfying rename.

## Risks / Trade-offs

- **[Risk] Large rename surface (sessions, Fortify, TS types)** → **Mitigation**: staged checklist in `tasks.md`, run full test suite, grep for `User` and `users` after changes.  
- **[Risk] Session foreign key rename vs Laravel defaults** → **Mitigation**: confirm Laravel version behavior for `sessions.user_id`; if immutable, document exception and add follow-up task to abstract session ownership.  
- **[Risk] Data migration in production** → **Mitigation**: provide `up`/`down` migrations that rename tables and FKs in a transaction where the database supports it.

## Migration Plan

1. Add new columns (`github_profile`, `status`, address fields) with defaults compatible with existing rows.  
2. Rename `users` → `developers` and rename foreign key columns per migration design.  
3. Deploy application code that targets `Developer` model and updated `auth.php` in the same release as migrations (or use expand-contract if zero-downtime is required — note in tasks if needed).  
4. Rollback: reverse migration restores `users` and prior FK names; restore previous model configuration.

## Open Questions

- Whether **email** remains the login identifier (assumed yes) and whether **name** maps to a public **display name** for the portal (assumed yes until UX spec exists).  
- Exact validation for `github_profile` (strict URL vs handle) — finalize in implementation against product preference.
