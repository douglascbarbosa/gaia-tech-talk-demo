<?php

namespace App\Application\Developer;

use App\Domain\Developer\Address;
use App\Domain\Developer\Developer;
use App\Domain\Developer\DeveloperRepositoryInterface;
use App\Domain\Developer\DeveloperStatus;
use App\Models\Developer as DeveloperModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

/**
 * Application-layer use cases for developer registration and profile maintenance.
 *
 * Orchestrates the domain repository and maps to the Eloquent {@see DeveloperModel} where the framework requires it.
 */
final class DeveloperApplicationService
{
    /**
     * @param  DeveloperRepositoryInterface  $developers  Domain persistence port
     */
    public function __construct(
        private readonly DeveloperRepositoryInterface $developers,
    ) {}

    /**
     * Create a new developer (registration / Fortify) and return the persisted Eloquent model.
     *
     * @param  CreateDeveloperData  $data  Validated registration payload
     * @throws ModelNotFoundException If the row cannot be reloaded after insert (unexpected)
     * @return DeveloperModel Persisted authenticatable model
     */
    public function create(CreateDeveloperData $data): DeveloperModel
    {
        $domain = Developer::register($data->name, $data->email);
        $created = $this->developers->create($domain, Hash::make($data->password));

        return DeveloperModel::query()->findOrFail($created->id());
    }

    /**
     * Load the developer aggregate by primary key.
     *
     * @param  int|string  $id  Primary key
     * @return Developer|null Aggregate when found
     */
    public function get(int|string $id): ?Developer
    {
        return $this->developers->findById($id);
    }

    /**
     * Update profile fields for the authenticated developer backed by the given model.
     *
     * @param  DeveloperModel  $model  Authenticated developer model (refreshed after update)
     * @param  array<string, mixed>  $validated  Output of {@see \App\Http\Requests\Settings\ProfileUpdateRequest::validated()}
     *
     * @throws ModelNotFoundException When no domain row exists for the model key
     * @return void
     */
    public function update(DeveloperModel $model, array $validated): void
    {
        $domain = $this->developers->findById($model->getKey());
        if ($domain === null) {
            throw (new ModelNotFoundException)->setModel(DeveloperModel::class, [$model->getKey()]);
        }

        $address = Address::tryFromRequestInput($validated);

        $github = $validated['github_profile'] ?? null;
        $github = is_string($github) && trim($github) === '' ? null : $github;

        $status = DeveloperStatus::from((string) $validated['status']);

        $updated = $domain->withUpdatedProfile(
            (string) $validated['name'],
            (string) $validated['email'],
            is_string($github) ? $github : null,
            $status,
            $address,
        );

        $this->developers->update($updated);
        $model->refresh();
    }

    /**
     * Delete the developer row for the authenticated account (e.g. Fortify profile destroy).
     *
     * @throws ModelNotFoundException When no domain row exists for the model key
     * @return void
     */
    public function delete(DeveloperModel $model): void
    {
        $domain = $this->developers->findById($model->getKey());
        if ($domain === null) {
            throw (new ModelNotFoundException)->setModel(DeveloperModel::class, [$model->getKey()]);
        }

        $this->developers->delete($domain);
    }
}
