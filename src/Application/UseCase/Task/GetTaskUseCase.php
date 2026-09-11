<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Entity\Task;
use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use InvalidArgumentException;

final class GetTaskUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $task_id, string $requester_id): Task
    {
        if (empty($task_id)) {
            throw new InvalidArgumentException('The task_id cannot be empty.');
        }

        if (empty($requester_id)) {
            throw new InvalidArgumentException('The requester_id cannot be empty.');
        }

        $task = $this->task_repository->findById($task_id);
        if (!$task) {
            throw new InvalidArgumentException('Task not found.');
        }

        $board_user = $this->board_user_repository->findByBoardAndUser($task->getBoardId(), $requester_id);
        if (!$board_user) {
            throw new InvalidArgumentException('User does not have permission to access this task.');
        }

        return $task;
    }
}
