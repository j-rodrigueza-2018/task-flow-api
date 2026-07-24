<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Repository\BoardRepository;
use InvalidArgumentException;

final class DeleteBoardUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository
    ) {}

    public function execute(string $board_id): void
    {
        $board = $this->board_repository->findById($board_id);
        if (!$board) {
            throw new InvalidArgumentException('Board not found.');
        }

        $board->delete();

        $this->board_repository->delete($board);
    }
}
