<?php

declare(strict_types=1);

namespace App\Application\UseCase\Task;

use App\Domain\Repository\TaskRepository;
use InvalidArgumentException;

final class GetUserTasksUseCase
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
    ) {}

    public function execute(string $user_id): array
    {
        if (empty($user_id)) {
            throw new InvalidArgumentException('The user_id cannot be empty.');
        }

        return $this->taskRepository->findByUserId($user_id);
    }
}
