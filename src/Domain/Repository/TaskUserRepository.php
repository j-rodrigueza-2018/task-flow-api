<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\TaskUser;

interface TaskUserRepository
{
    /**
     * Saves or updates the relationship between a user with a task.
     * 
     * @param TaskUser $task_user The relationship to save.
     * 
     * @return void
     */
    public function save(TaskUser $task_user): void;

    /**
     * Deletes the relationship between a user and a task.
     * 
     * @param string $task_id The ID of the task.
     * @param string $user_id The ID of the user.
     * 
     * @return void
     */
    public function delete(string $task_id, string $user_id): void;

    /**
     * Finds the relationship between a user with a task by task's ID and user's ID.
     * 
     * @param string $task_id The ID of the task.
     * @param string $user_id The ID of the user.
     * 
     * @return TaskUser|null The found relationship or null if not found.
     */
    public function findByTaskAndUser(string $task_id, string $user_id): ?TaskUser;
}
