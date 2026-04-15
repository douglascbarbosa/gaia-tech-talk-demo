<?php

namespace App\Infrastructure\Persistence\Developer;

use App\Domain\Developer\Developer;
use App\Domain\Developer\DeveloperRepositoryInterface;
use App\Models\Developer as DeveloperModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * {@see DeveloperRepositoryInterface} backed by Eloquent and {@see DeveloperMapper}.
 */
final class EloquentDeveloperRepository implements DeveloperRepositoryInterface
{
    /**
     * @param  DeveloperMapper  $mapper  Domain ↔ model mapper
     */
    public function __construct(
        private readonly DeveloperMapper $mapper,
    ) {}

    /**
     * @param  int|string  $id  Primary key
     * @return Developer|null Domain aggregate when a row exists
     */
    public function findById(int|string $id): ?Developer
    {
        $model = DeveloperModel::query()->find($id);

        return $model ? $this->mapper->fromModel($model) : null;
    }

    /**
     * @param  Developer  $developer  New aggregate without persisted id
     * @param  string  $hashedPassword  Hashed password for the new row
     * @return Developer Aggregate including assigned primary key
     */
    public function create(Developer $developer, string $hashedPassword): Developer
    {
        $model = new DeveloperModel;
        $this->mapper->mapNewRegistration($developer, $model, $hashedPassword);
        $model->save();

        return $this->mapper->fromModel($model->fresh());
    }

    /**
     * @param  Developer  $developer  Aggregate including primary key and changed attributes
     *
     * @throws \InvalidArgumentException When {@see Developer::id()} is null
     * @throws ModelNotFoundException When no row exists for the given id
     * @return void
     */
    public function update(Developer $developer): void
    {
        if ($developer->id() === null) {
            throw new \InvalidArgumentException('Cannot update a developer without an id.');
        }

        $model = DeveloperModel::query()->findOrFail($developer->id());

        $this->mapper->mapOntoModel($developer, $model);

        if ($model->isDirty('email')) {
            $model->email_verified_at = null;
        }

        $model->save();
    }

    /**
     * @param  Developer  $developer  Aggregate whose primary key row should be removed
     *
     * @throws \InvalidArgumentException When {@see Developer::id()} is null
     * @return void
     */
    public function delete(Developer $developer): void
    {
        if ($developer->id() === null) {
            throw new \InvalidArgumentException('Cannot delete a developer without an id.');
        }

        DeveloperModel::query()->whereKey($developer->id())->delete();
    }
}
