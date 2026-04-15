<?php

namespace App\Domain\Developer;

use InvalidArgumentException;

/**
 * Physical contact address for a developer.
 *
 * Invariant: either all four components are non-empty strings, or the address is absent (null).
 * Partial addresses (some fields set, others empty) are invalid.
 */
final readonly class Address
{
    /**
     * @param  string  $street  Street line (non-empty when address is present).
     * @param  string  $city  City name.
     * @param  string  $postalCode  Postal or ZIP code.
     * @param  string  $country  Country name or code, per application rules.
     */
    public function __construct(
        public string $street,
        public string $city,
        public string $postalCode,
        public string $country,
    ) {}

    /**
     * Build an address from HTTP request-style keys, or null when all components are absent.
     *
     * @param  array<string, mixed>  $input  Keys: address_street, address_city, address_postal_code, address_country
     * @return self|null Null when no address fields are provided
     *
     * @throws InvalidArgumentException When only a subset of address fields is provided
     */
    public static function tryFromRequestInput(array $input): ?self
    {
        $street = self::normalize($input['address_street'] ?? null);
        $city = self::normalize($input['address_city'] ?? null);
        $postal = self::normalize($input['address_postal_code'] ?? null);
        $country = self::normalize($input['address_country'] ?? null);

        $hasAny = $street !== null || $city !== null || $postal !== null || $country !== null;
        $hasAll = $street !== null && $city !== null && $postal !== null && $country !== null;

        if (! $hasAny) {
            return null;
        }

        if (! $hasAll) {
            throw new InvalidArgumentException('Address fields must all be provided together.');
        }

        return new self($street, $city, $postal, $country);
    }

    /**
     * Trim a scalar input to a non-empty string, or null when absent or blank.
     *
     * @param  mixed  $value  Raw input (typically string|null from request)
     * @return string|null Non-empty trimmed string, or null
     */
    private static function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }
}
