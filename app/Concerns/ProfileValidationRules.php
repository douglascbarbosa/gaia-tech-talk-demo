<?php

namespace App\Concerns;

use App\Models\Developer;
use Illuminate\Validation\Rule;

/**
 * Reusable validation rules for developer profile name and email (registration and settings).
 */
trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate developer profile core fields.
     *
     * @param  int|null  $developerId  When updating, pass the current developer id for unique email ignore rules
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $developerId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($developerId),
        ];
    }

    /**
     * Get the validation rules used to validate developer names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate developer emails against the `developers` table.
     *
     * @param  int|null  $developerId  When non-null, the unique rule ignores this id (profile update)
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $developerId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $developerId === null
                ? Rule::unique(Developer::class)
                : Rule::unique(Developer::class)->ignore($developerId),
        ];
    }
}
