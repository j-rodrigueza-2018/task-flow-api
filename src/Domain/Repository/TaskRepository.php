<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Task;

interface TaskRepository
{
    /**
     * Saves a task to the repository.
     *
     * @param Task $task The task to save.
     *
     * @return void
     */
    public function save(Task $task): void;

    /**
     * Deletes a task from the repository.
     *
     * @param Task $task The task to delete.
     *
     * @return void
     */
    public function delete(Task $task): void;

    /**
     * Finds a task by its ID.
     *
     * @param string $id The ID of the task to find.
     *
     * @return Task|null The found task or null if not found.
     */
    public function findById(string $id): ?Task;

    /**
     * Finds all the user's tasks in the repository.
     *
     * @param string $user_id The ID of the user whose tasks to find.
     *
     * @return Task[] An array of all the user's tasks.
     */
    public function findByUserId(string $user_id): array;
}
