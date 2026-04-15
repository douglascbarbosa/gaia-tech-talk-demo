<?php

namespace App\Application\Developer;

/**
 * Validated input for creating a new developer account (e.g. Fortify registration).
 */
final readonly class CreateDeveloperData
{
    /**
     * @param  string  $name  Display or legal name
     * @param  string  $email  Unique login email
     * @param  string  $password  Plain password (hashed by the application service before persistence)
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * Build DTO from a validated registration payload.
     *
     * @param  array{name: string, email: string, password: string}  $input
     * @return self DTO instance
     */
    public static function fromArray(array $input): self
    {
        return new self($input['name'], $input['email'], $input['password']);
    }
}
