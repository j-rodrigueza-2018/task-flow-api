<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Repository\BoardRepository;
use InvalidArgumentException;

final class GetUserBoardsUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository
    ) {}

    public function execute(string $requester_id): array
    {
        if (empty($requester_id)) {
            throw new InvalidArgumentException('The requester_id cannot be empty.');
        }

        return $this->board_repository->findByUserId($requester_id);
    }
}
