<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Repository\TaskRepository;

final class GetUserTasksUseCase
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
    ) {}

    public function execute(string $user_id): array
    {
        return $this->taskRepository->findByUserId($user_id);
    }
}
