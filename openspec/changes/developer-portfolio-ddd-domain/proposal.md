## Why

The product direction is a **Developer Portfolio Portal**: a place where a developer showcases projects and experience and signals whether they are open to new opportunities. The codebase still centers on a generic **User** model and lacks an explicit **domain layer**, which makes ubiquitous language and future portfolio features harder to evolve safely. Establishing DDD boundaries, renaming the core identity to **Developer**, and adding structured profile data (GitHub, availability, address) aligns the implementation with the domain we are building.

## Non-goals

- Full portfolio UI for projects and experience timelines (this change establishes domain and developer profile persistence only).
- Public developer directory, search, or social features.
- Replacing Laravel Fortify authentication flows beyond what is required to keep login working with the renamed identity table/model.

## What Changes

- Introduce a **domain layer** (entities, value objects, domain interfaces) for the Developer Portfolio bounded context, with clear separation from infrastructure and application orchestration.
- **BREAKING**: Replace ubiquitous-language **User** with **Developer** for the authenticated domain identity, including **database table rename** (`users` → `developers`) and model rename, with cascading updates to foreign keys (for example `sessions.user_id`), auth configuration, factories, seeders, and TypeScript types where they represent the authenticated person.
- Add **Developer** profile fields: `github_profile` (URL or handle per design), **status** with allowed values **open for new jobs** and **working**, and **address** modeled as a **value object** with `street`, `city`, `postal_code`, and `country`.
- Implement a **DeveloperRepository** (interface in domain, concrete persistence in infrastructure) and **application-level services** (or use-case services) that perform **create, read, update, delete** for the Developer aggregate as defined in specs.

## Capabilities

### New Capabilities

- `developer-portfolio`: Ubiquitous language and bounded-context foundation for the Developer Portfolio Portal, including DDD layering, Developer identity and profile (GitHub, availability status, Address value object), persistence rename from User, repository contract, and application services for Developer CRUD.

### Modified Capabilities

- _(none — no existing capability specs under `openspec/specs/`.)_

## Impact

- **Laravel**: `App\Models\User`, `database/migrations` for `users` and dependents, `config/auth.php`, Fortify user creation, profile/settings controllers, policies, and tests referencing `User`.
- **Frontend**: shared types such as `resources/js/types/auth.ts` and any components assuming a `User` shape.
- **Database**: migration strategy for renaming table/columns and preserving existing authentication data where applicable.
