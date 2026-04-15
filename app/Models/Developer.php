<?php

namespace App\Models;

use Database\Factories\DeveloperFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Authenticatable developer persisted in the `developers` table (Fortify / session guard).
 *
 * Used as the framework-facing identity; the domain layer uses {@see \App\Domain\Developer\Developer}.
 */
#[Fillable([
    'name',
    'email',
    'password',
    'github_profile',
    'status',
    'address_street',
    'address_city',
    'address_postal_code',
    'address_country',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class Developer extends Authenticatable
{
    /** @use HasFactory<DeveloperFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * @var string Database table name (distinct from Laravel's default `users`).
     */
    protected $table = 'developers';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return DeveloperFactory Factory for test and seed data
     */
    protected static function newFactory(): DeveloperFactory
    {
        return DeveloperFactory::new();
    }
}
