<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use App\Domain\Developer\Address;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;

/**
 * Validates profile updates including portfolio fields and address invariants.
 */
class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            'github_profile' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:open_for_new_jobs,working'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:255'],
            'address_postal_code' => ['nullable', 'string', 'max:32'],
            'address_country' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Enforce that address fields are either all empty or all present via the domain value object.
     *
     * @param  Validator  $validator  Laravel validator instance being built
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            try {
                Address::tryFromRequestInput($validator->getData());
            } catch (InvalidArgumentException $e) {
                $validator->errors()->add('address_street', $e->getMessage());
            }
        });
    }
}
