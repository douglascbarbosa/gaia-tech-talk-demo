<?php

namespace App\Actions\Fortify;

use App\Application\Developer\CreateDeveloperData;
use App\Application\Developer\DeveloperApplicationService;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Developer;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Fortify hook: validates registration input and persists a new {@see Developer} via the application service.
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * @param  DeveloperApplicationService  $developerApplicationService  Registration use case
     */
    public function __construct(
        private readonly DeveloperApplicationService $developerApplicationService,
    ) {}

    /**
     * Validate and create a newly registered developer.
     *
     * @param  array<string, string>  $input  Fortify registration payload (name, email, password, …)
     * @return Developer Persisted authenticatable model
     */
    public function create(array $input): Developer
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return $this->developerApplicationService->create(
            CreateDeveloperData::fromArray([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ])
        );
    }
}
