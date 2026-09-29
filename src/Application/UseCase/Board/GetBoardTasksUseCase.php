<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use DomainException;
use InvalidArgumentException;

final class GetBoardTasksUseCase
{
    public function __construct(
        private readonly TaskRepository $task_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $board_id, string $requester_id): array
    {
        if (empty($board_id)) {
            throw new InvalidArgumentException('The board_id cannot be empty.');
        }

        if (empty($requester_id)) {
            throw new InvalidArgumentException('The requester_id cannot be empty.');
        }

        $board_user = $this->board_user_repository->findByBoardAndUser($board_id, $requester_id);
        if (!$board_user) {
            throw new DomainException('Requester does not have permission to access this board.');
        }

        return $this->task_repository->findByBoardId($board_id);
    }
}
