<?php

namespace App\Domain\Developer;

/**
 * Developer aggregate root for the portfolio bounded context (identity and profile fields).
 *
 * This type is persistence-agnostic; infrastructure maps it to the Eloquent {@see \App\Models\Developer} model.
 */
final class Developer
{
    /**
     * @param  ?int  $id  Primary key when loaded from persistence; null for not-yet-persisted instances
     * @param  string  $name  Display or legal name
     * @param  string  $email  Login email
     * @param  ?string  $githubProfile  Public GitHub URL or handle
     * @param  DeveloperStatus  $status  Availability for new roles
     * @param  ?Address  $address  Optional mailing-style address
     */
    public function __construct(
        private ?int $id,
        private string $name,
        private string $email,
        private ?string $githubProfile,
        private DeveloperStatus $status,
        private ?Address $address,
    ) {}

    /**
     * Start a new developer registration with default status and no optional profile fields.
     *
     * @param  string  $name  Display or legal name
     * @param  string  $email  Login email
     * @return self Aggregate without id and default status
     */
    public static function register(string $name, string $email): self
    {
        return new self(null, $name, $email, null, DeveloperStatus::Working, null);
    }

    /**
     * @return int|null Primary key when persisted or known
     */
    public function id(): ?int
    {
        return $this->id;
    }

    /**
     * Return a copy with the persisted identifier set (e.g. after insert).
     *
     * @param  int  $id  Assigned primary key
     * @return self Copy with id set
     */
    public function withId(int $id): self
    {
        return new self($id, $this->name, $this->email, $this->githubProfile, $this->status, $this->address);
    }

    /**
     * @return string Display or legal name
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string Login email
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     * @return string|null GitHub profile URL or handle when set
     */
    public function githubProfile(): ?string
    {
        return $this->githubProfile;
    }

    /**
     * @return DeveloperStatus Current availability status
     */
    public function status(): DeveloperStatus
    {
        return $this->status;
    }

    /**
     * @return Address|null Structured address when fully provided
     */
    public function address(): ?Address
    {
        return $this->address;
    }

    /**
     * Return a copy with profile scalar fields and address replaced.
     *
     * @param  string  $name  Updated name
     * @param  string  $email  Updated email
     * @param  ?string  $githubProfile  Updated GitHub profile or null
     * @param  DeveloperStatus  $status  Updated availability status
     * @param  ?Address  $address  Updated address or null to clear
     * @return self Copy with updated profile fields
     */
    public function withUpdatedProfile(
        string $name,
        string $email,
        ?string $githubProfile,
        DeveloperStatus $status,
        ?Address $address,
    ): self {
        return new self($this->id, $name, $email, $githubProfile, $status, $address);
    }
}
