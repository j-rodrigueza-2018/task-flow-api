<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use App\Domain\Repository\TaskUserRepository;
use InvalidArgumentException;

final class DeleteUserFromTaskUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly TaskUserRepository $task_user_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $task_id, string $user_id, string $requester_id): void
    {
        if (empty($task_id)) {
            throw new InvalidArgumentException('The task_id cannot be empty.');
        }

        if (empty($user_id)) {
            throw new InvalidArgumentException('The user_id cannot be empty.');
        }

        if (empty($requester_id)) {
            throw new InvalidArgumentException('The requester_id cannot be empty.');
        }

        $task = $this->task_repository->findById($task_id);
        if (!$task) {
            throw new InvalidArgumentException('The task does not exist.');
        }

        $board_member = $this->board_user_repository->findByBoardAndUser($task->getBoardId(), $requester_id);
        if (!$board_member) {
            throw new InvalidArgumentException('You do not have permission to remove users from this task.');
        }

        $task_user = $this->task_user_repository->findByTaskAndUser($task_id, $user_id);
        if (!$task_user) {
            throw new InvalidArgumentException('The user is not related to this task');
        }

        $this->task_user_repository->delete($task_id, $user_id);
    }
}
