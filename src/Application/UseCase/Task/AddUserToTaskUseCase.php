<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Entity\TaskUser;
use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use App\Domain\Repository\TaskUserRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final class AddUserToTaskUseCase
{
    public function __construct(
        private readonly TaskUserRepository $task_user_repository,
        private readonly TaskRepository $task_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $task_id, string $user_id): TaskUser
    {
        if (empty($task_id)) {
            throw new InvalidArgumentException('The task_id cannot be empty.');
        }

        if (empty($user_id)) {
            throw new InvalidArgumentException('The user_id cannot be empty.');
        }

        $task = $this->task_repository->findById($task_id);
        if (!$task) {
            throw new InvalidArgumentException('The task does not exist.');
        }

        $board_member = $this->board_user_repository->findByBoardAndUser($task->getBoardId(), $user_id);
        if (!$board_member) {
            throw new InvalidArgumentException('The user must be a member of the board to be assigned to its tasks.');
        }

        $task_member = $this->task_user_repository->findByTaskAndUser($task_id, $user_id);
        if ($task_member) {
            throw new InvalidArgumentException('The user is already assigned to this task.');
        }

        $task_user = new TaskUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            task_id: $task_id,
            user_id: $user_id,
            created_at: new DateTimeImmutable()
        );

        $this->task_user_repository->save($task_user);

        return $task_user;
    }
}
