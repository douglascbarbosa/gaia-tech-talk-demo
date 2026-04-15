<?php

namespace App\Domain\Developer;

/**
 * Port for persisting and loading {@see Developer} aggregate instances.
 *
 * Implementations live in the infrastructure layer (e.g. Eloquent).
 */
interface DeveloperRepositoryInterface
{
    /**
     * Load a developer by primary key.
     *
     * @param  int|string  $id  Numeric primary key
     * @return Developer|null Domain aggregate when a row exists
     */
    public function findById(int|string $id): ?Developer;

    /**
     * Insert a new developer row and return the aggregate including assigned id.
     *
     * @param  Developer  $developer  Domain state without persisted id
     * @param  string  $hashedPassword  Already-hashed password for the credentials column
     * @return Developer Aggregate including assigned primary key
     */
    public function create(Developer $developer, string $hashedPassword): Developer;

    /**
     * Persist changes to an existing developer.
     *
     * @param  Developer  $developer  Aggregate with non-null {@see Developer::id()}
     * @return void
     */
    public function update(Developer $developer): void;

    /**
     * Remove the developer row for this aggregate.
     *
     * @param  Developer  $developer  Aggregate with non-null {@see Developer::id()}
     * @return void
     */
    public function delete(Developer $developer): void;
}
