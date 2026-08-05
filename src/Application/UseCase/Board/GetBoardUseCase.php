<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Entity\Board;
use App\Domain\Repository\BoardRepository;
use App\Domain\Repository\BoardUserRepository;
use InvalidArgumentException;

final class GetBoardUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $board_id, string $user_id): Board
    {
        $board = $this->board_repository->findById($board_id);
        if (!$board) {
            throw new InvalidArgumentException('Board not found.');
        }

        $board_user = $this->board_user_repository->findByBoardAndUser($board_id, $user_id);
        if (!$board_user) {
            throw new InvalidArgumentException('User does not have permission to access this board.');
        }

        return $board;
    }
}
