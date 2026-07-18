<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepository;
use DateTimeImmutable;

final class CreateTaskUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository
    ) {}

    public function execute(string $user_id, string $board_id, string $title, ?string $description = null): Task
    {
        $task = new Task(
            uuid_create(UUID_TYPE_RANDOM),
            $user_id,
            $board_id,
            $title,
            $description,
            Task::STATUS_PENDING,
            new DateTimeImmutable(),
            new DateTimeImmutable()
        );

        $this->task_repository->save($task);

        return $task;
    }
}
