<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\Developer;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/**
 * Fortify hook: validates and applies a new password for a {@see Developer} after password reset.
 */
class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the developer's forgotten password.
     *
     * @param  Developer  $user  Authenticated identity model (Fortify contract name retained as `$user`)
     * @param  array<string, string>  $input  Must include validated `password`
     */
    public function reset(Developer $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => $input['password'],
        ])->save();
    }
}
