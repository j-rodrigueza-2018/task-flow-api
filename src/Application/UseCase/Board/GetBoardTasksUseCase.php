<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use InvalidArgumentException;

final class GetBoardTasksUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $board_id, string $user_id): array
    {
        $board_user = $this->board_user_repository->findByBoardAndUser($board_id, $user_id);
        if (!$board_user) {
            throw new InvalidArgumentException('User does not have permission to access this board.');
        }

        return $this->task_repository->findByBoardId($board_id);
    }
}
