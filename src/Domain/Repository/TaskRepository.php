<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Task;

interface TaskRepository
{
    public function save(Task $task): void;
    public function delete(Task $task): void;
    public function findById(string $id): ?Task;
    public function findByUserId(string $user_id): array;
}
