<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepository;
use App\Domain\Repository\TaskUserRepository;
use InvalidArgumentException;

final class GetTaskUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly TaskUserRepository $task_user_repository
    ) {}

    public function execute(string $task_id, string $user_id): Task
    {
        $task = $this->task_repository->findById($task_id);
        if (!$task) {
            throw new InvalidArgumentException('Task not found.');
        }

        $task_user = $this->task_user_repository->findByTaskAndUser($task_id, $user_id);
        if (!$task_user) {
            throw new InvalidArgumentException('User does not have permission to access this task.');
        }

        return $task;
    }
}
