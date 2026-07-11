<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepository;

final class UpdateTaskUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository
    ) {}

    public function execute(string $task_id, string $user_id, ?string $title, ?string $description, ?string $status): Task
    {
        $task = $this->task_repository->findById($task_id);

        if (!$task || $task->getUserId() !== $user_id) {
            throw new \InvalidArgumentException('Task not found or user does not have permission to update this task.');
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

        $this->task_repository->save($task);

        return $task;
    }
}
