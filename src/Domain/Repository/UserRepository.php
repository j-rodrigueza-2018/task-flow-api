<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepository
{
    /**
     * Saves a user to the repository.
     *
     * @param User $user The user to save.
     *
     * @return void
     */
    public function save(User $user): void;

    /**
     * Deletes a user from the repository.
     *
     * @param User $user The user to delete.
     *
     * @return void
     */
    public function delete(User $user): void;

    /**
     * Finds a user by their ID.
     *
     * @param string $id The ID of the user to find.
     *
     * @return User|null The found user or null if not found.
     */
    public function findById(string $id): ?User;

    /**
     * Finds a user by their email.
     *
     * @param string $email The email of the user to find.
     *
     * @return User|null The found user or null if not found.
     */
    public function findByEmail(string $email): ?User;
}
