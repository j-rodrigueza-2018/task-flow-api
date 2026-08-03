<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Entity\Task;
use App\Domain\Entity\TaskUser;
use App\Domain\Repository\TaskRepository;
use App\Domain\Repository\TaskUserRepository;
use DateTimeImmutable;

final class CreateTaskUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly TaskUserRepository $task_user_repository
    ) {}

    public function execute(
        string $user_id,
        string $board_id,
        string $title,
        ?string $description = null
    ): Task {
        $task = new Task(
            id: uuid_create(UUID_TYPE_RANDOM),
            title: $title,
            description: $description,
            status: Task::STATUS_PENDING,
            board_id: $board_id,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->task_repository->save($task);

        $task_user = new TaskUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            task_id: $task->getId(),
            user_id: $user_id,
            created_at: new DateTimeImmutable()
        );

        $this->task_user_repository->save($task_user);

        return $task;
    }
}
