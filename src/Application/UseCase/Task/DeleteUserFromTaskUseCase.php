<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Repository\TaskUserRepository;
use InvalidArgumentException;

final class DeleteUserFromTaskUseCase
{
    public function __construct(
        private readonly TaskUserRepository $task_user_repository
    ) {}

    public function execute(string $task_id, string $user_id): void
    {
        if (empty($task_id)) {
            throw new InvalidArgumentException('The task_id cannot be empty.');
        }

        if (empty($user_id)) {
            throw new InvalidArgumentException('The user_id cannot be empty.');
        }

        $task_user = $this->task_user_repository->findByTaskAndUser($task_id, $user_id);
        if (!$task_user) {
            throw new InvalidArgumentException('The user is not related to this task');
        }

        $this->task_user_repository->delete($task_id, $user_id);
    }
}
