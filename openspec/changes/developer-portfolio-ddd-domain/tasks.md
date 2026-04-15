## 1. Domain structure and Developer model

- [ ] 1.1 Add `app/Domain/Developer/` with namespaces documented in `design.md` (entity home for VO and interfaces).
- [ ] 1.2 Implement `Address` value object with `street`, `city`, `postalCode`, `country` (constructor or named factory; document all-or-nothing vs optional policy to match spec).
- [ ] 1.3 Implement domain `Developer` entity (identity, email/name linkage as needed, embedded or associated `Address`, `githubProfile`, `status` with allowed values).
- [ ] 1.4 Add `DeveloperRepositoryInterface` (or equivalent port name) with methods for find-by-id, save, delete as required by CRUD use cases.

## 2. Database migration and schema

- [ ] 2.1 Draft migration to rename `users` → `developers` and add columns `github_profile`, `status`, `address_street`, `address_city`, `address_postal_code`, `address_country` with sensible nullability and default for `status`.
- [ ] 2.2 Update `sessions` (and any other tables) referencing `user_id` to reference `developer_id` (or chosen consistent name); verify Laravel session configuration compatibility.
- [ ] 2.3 Update `password_reset_tokens` or related auth tables only if they reference `users` / `user_id` (keep Fortify working).
- [ ] 2.4 Run migrations locally and confirm rollback path.

## 3. Eloquent model and auth wiring

- [ ] 3.1 Replace `App\Models\User` with `App\Models\Developer` extending `Authenticatable`, updating `$table`, fillable/hidden/casts, and two-factor fields parity.
- [ ] 3.2 Update `config/auth.php` provider model and any `AUTH_MODEL` env usage to `Developer`.
- [ ] 3.3 Update Fortify actions (`CreateNewUser`, etc.) and profile routes to use `Developer` naming where they bind to the authenticated model.

## 4. Infrastructure repository and mapping

- [ ] 4.1 Implement `EloquentDeveloperRepository` (or equivalent) under `app/Infrastructure/Persistence/Developer/` implementing `DeveloperRepositoryInterface`.
- [ ] 4.2 Add mapper between Eloquent `Developer` model and domain `Developer` entity (including Address VO and status enum mapping).
- [ ] 4.3 Register repository binding in a service provider.

## 5. Application CRUD service

- [ ] 5.1 Implement `DeveloperApplicationService` (or similarly named use-case class) with `create`, `get`, `update`, `delete` methods calling repository + domain rules.
- [ ] 5.2 Add input DTOs or structured parameters and validation for `github_profile`, `status`, and Address fields at the application boundary.
- [ ] 5.3 Wire controllers or existing settings endpoints to delegate persistence of profile fields to the application service (avoid ad hoc `Developer::query()->update` in controllers for covered fields).

## 6. Supporting code and tests

- [ ] 6.1 Rename/update `UserFactory` → `DeveloperFactory`, `DatabaseSeeder`, and any policies or `Authorizable` references.
- [ ] 6.2 Update `resources/js/types/auth.ts` (and related) from `User` to `Developer` shape where it reflects the backend model.
- [ ] 6.3 Add or update Pest/PHPUnit feature tests for registration/login still passing and for CRUD paths on Developer profile fields.
- [ ] 6.4 Repository-wide search for `User`, `users`, and `user_id` tied to this aggregate; fix stragglers and update developer-facing docs/comments only where necessary.
