<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepository;
use App\Domain\Repository\TaskUserRepository;
use InvalidArgumentException;

final class UpdateTaskUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly TaskUserRepository $task_user_repository
    ) {}

    public function execute(string $task_id, string $user_id, ?string $title, ?string $description, ?string $status, ?string $board_id): Task
    {
        $task = $this->task_repository->findById($task_id);

        if (!$task) {
            throw new InvalidArgumentException('Task not found.');
        }

        $task_user = $this->task_user_repository->findByTaskAndUser($task_id, $user_id);
        if (!$task_user) {
            throw new InvalidArgumentException('User does not have permission to update this task.');
        }

        if ($title !== null) {
            $task->updateTitle($title);
        }

        if ($description !== null) {
            $task->updateDescription($description);
        }

        if ($status !== null) {
            $task->updateStatus($status);
        }

        if ($board_id !== null) {
            $task->moveToBoard($board_id);
        }

        $this->task_repository->save($task);

        return $task;
    }
}
