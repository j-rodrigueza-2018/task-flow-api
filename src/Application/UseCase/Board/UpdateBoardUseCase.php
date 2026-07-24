<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Entity\Board;
use App\Domain\Repository\BoardRepository;
use InvalidArgumentException;

final class UpdateBoardUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository
    ) {}

    public function execute(string $board_id, ?string $name, ?string $description): Board
    {
        $board = $this->board_repository->findById($board_id);
        if (!$board) {
            throw new InvalidArgumentException('Board not found.');
        }

        $has_changes = false;

        if ($name !== null) {
            $board->updateName($name);
            $has_changes = true;
        }

        if ($description !== null) {
            $board->updateDescription($description);
            $has_changes = true;
        }

        if ($has_changes) {
            $this->board_repository->save($board);
        }

        return $board;
    }
}
