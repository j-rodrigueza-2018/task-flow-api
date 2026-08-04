<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Repository\BoardRepository;

final class GetUserBoardsUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository
    ) {}

    public function execute(string $user_id): array
    {
        return $this->board_repository->findByUserId($user_id);
    }
}
