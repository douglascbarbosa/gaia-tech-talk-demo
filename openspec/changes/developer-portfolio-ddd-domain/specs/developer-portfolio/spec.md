## ADDED Requirements

### Requirement: Domain layer for Developer Portfolio

The system MUST introduce a dedicated **domain layer** for the Developer Portfolio bounded context so that framework and persistence details do not define core business terminology or invariants.

#### Scenario: Domain code is isolated from infrastructure

- **WHEN** a developer inspects the codebase structure for the Developer aggregate
- **THEN** entity and value object classes for the Developer Portfolio context MUST reside under a `Domain` namespace (for example `App\Domain\Developer`) separate from Eloquent-specific mapping classes except at explicit mapping boundaries

### Requirement: Ubiquitous language uses Developer not User

The system MUST use the term **Developer** for the authenticated portfolio identity in domain naming, primary persistence table name, and related foreign keys, instead of **User**, except where a third-party package requires a fixed identifier string that cannot be changed without unacceptable fork cost (such identifier strings MUST be documented in code comments if used).

#### Scenario: Persistence table reflects Developer

- **WHEN** the database schema is inspected after migration
- **THEN** the primary table for authenticated portfolio identities MUST be named `developers` (or equivalent pluralization consistent with project conventions) and MUST NOT retain `users` as that table’s name for this aggregate

### Requirement: Address value object

The system MUST represent a physical mailing or contact location as an **Address** value object composed of **street**, **city**, **postal code**, and **country**, and the Developer aggregate MUST expose address only through this value object or equivalent typed structure at the domain boundary.

#### Scenario: Address fields travel together

- **WHEN** application code sets or updates a Developer’s address
- **THEN** street, city, postal code, and country MUST be supplied and validated as a single conceptual unit mapped to the Address value object rules defined in implementation

#### Scenario: Partial address rejection

- **WHEN** a client attempts to persist an Address with only a subset of the four components populated and the domain rule requires completeness
- **THEN** the operation MUST fail validation or domain construction before persistence

### Requirement: Developer availability status

The system MUST persist and expose a **status** on each Developer that indicates employment availability using exactly the canonical values **`open_for_new_jobs`** and **`working`** at the persistence boundary (storage and public application DTOs MUST use these identifiers or a documented equivalent mapping).

#### Scenario: Allowed status values

- **WHEN** a Developer’s status is created or updated
- **THEN** the stored value MUST be either `open_for_new_jobs` or `working`

#### Scenario: Reject unknown status

- **WHEN** a request attempts to assign any other status value
- **THEN** the system MUST reject the change with a validation or domain error and MUST NOT persist the invalid value

### Requirement: GitHub profile on Developer

The system MUST store a **`github_profile`** attribute for each Developer representing their GitHub presence (URL and/or handle per validation rules in the application layer).

#### Scenario: Persist github profile

- **WHEN** a valid `github_profile` value is submitted on create or update
- **THEN** the system MUST persist it on the Developer record and return it on subsequent reads

### Requirement: Developer repository abstraction

The system MUST define a **DeveloperRepository** interface in the domain or application port boundary and MUST provide an infrastructure implementation that performs persistence for the Developer aggregate without leaking Eloquent query builder types across that boundary.

#### Scenario: Repository supports lookup by identifier

- **WHEN** application code requests a Developer by primary identifier
- **THEN** the repository implementation MUST return the aggregate when present or an explicit not-found result consistent with application error handling

### Requirement: Application service CRUD for Developer

The system MUST provide an **application-level service** (use-case layer) that orchestrates **create, read, update, and delete** operations for Developers through the repository and domain constructors, rather than performing ad hoc Eloquent updates directly from HTTP controllers for these operations.

#### Scenario: Create developer via service

- **WHEN** the application creates a new Developer through the service with valid input
- **THEN** a new Developer record MUST exist in persistence and domain invariants for Address and status MUST be enforced

#### Scenario: Update developer via service

- **WHEN** the application updates an existing Developer through the service
- **THEN** persisted fields MUST reflect the update and invalid combinations MUST be rejected before persistence

#### Scenario: Delete developer via service

- **WHEN** the application deletes a Developer through the service
- **THEN** the Developer MUST no longer be retrievable by the repository’s primary lookup and related cleanup rules (for example sessions) MUST be applied as defined in design
