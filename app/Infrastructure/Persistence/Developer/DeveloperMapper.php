<?php

namespace App\Infrastructure\Persistence\Developer;

use App\Domain\Developer\Address;
use App\Domain\Developer\Developer;
use App\Domain\Developer\DeveloperStatus;
use App\Models\Developer as DeveloperModel;
use InvalidArgumentException;

/**
 * Maps between the domain {@see Developer} aggregate and the {@see DeveloperModel} Eloquent entity.
 */
final class DeveloperMapper
{
    /**
     * Hydrate a domain developer from a loaded Eloquent model.
     *
     * @param  DeveloperModel  $model  Persisted row
     * @return Developer Domain aggregate
     */
    public function fromModel(DeveloperModel $model): Developer
    {
        $address = $this->addressFromModel($model);

        return new Developer(
            (int) $model->getKey(),
            (string) $model->name,
            (string) $model->email,
            $model->github_profile !== null ? (string) $model->github_profile : null,
            DeveloperStatus::from((string) $model->status),
            $address,
        );
    }

    /**
     * Copy domain profile fields onto an existing Eloquent model instance (insert or update).
     *
     * @param  Developer  $developer  Source aggregate
     * @param  DeveloperModel  $model  Target model (unsaved changes applied in memory)
     * @return void
     */
    public function mapOntoModel(Developer $developer, DeveloperModel $model): void
    {
        $model->name = $developer->name();
        $model->email = $developer->email();
        $model->github_profile = $developer->githubProfile();
        $model->status = $developer->status()->value;

        $addr = $developer->address();
        if ($addr === null) {
            $model->address_street = null;
            $model->address_city = null;
            $model->address_postal_code = null;
            $model->address_country = null;
        } else {
            $model->address_street = $addr->street;
            $model->address_city = $addr->city;
            $model->address_postal_code = $addr->postalCode;
            $model->address_country = $addr->country;
        }
    }

    /**
     * Map a new registration aggregate and assign the hashed password before the first save.
     *
     * @param  string  $hashedPassword  Bcrypt (or configured) hash for the password column
     * @return void
     */
    public function mapNewRegistration(Developer $developer, DeveloperModel $model, string $hashedPassword): void
    {
        $this->mapOntoModel($developer, $model);
        $model->password = $hashedPassword;
    }

    /**
     * Rebuild an {@see Address} from nullable columns, or null when all are empty.
     *
     * Returns null when stored data is inconsistent (partial rows) instead of throwing.
     *
     * @param  DeveloperModel  $model  Row possibly holding address columns
     * @return Address|null Value object or null when absent or inconsistent
     */
    private function addressFromModel(DeveloperModel $model): ?Address
    {
        $street = $model->address_street;
        $city = $model->address_city;
        $postal = $model->address_postal_code;
        $country = $model->address_country;

        if ($street === null && $city === null && $postal === null && $country === null) {
            return null;
        }

        try {
            return Address::tryFromRequestInput([
                'address_street' => $street,
                'address_city' => $city,
                'address_postal_code' => $postal,
                'address_country' => $country,
            ]);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
