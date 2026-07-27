<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Repository\BoardUserRepository;
use InvalidArgumentException;

final class DeleteUserFromBoardUseCase
{
    public function __construct(
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $board_id, string $user_id): void
    {
        if (empty($board_id)) {
            throw new InvalidArgumentException('The board_id cannot be empty.');
        }

        if (empty($user_id)) {
            throw new InvalidArgumentException('The user_id cannot be empty.');
        }

        $existing_member = $this->board_user_repository->findByBoardAndUser($board_id, $user_id);
        if (!$existing_member) {
            throw new InvalidArgumentException('The user is not a member of this board.');
        }

        $this->board_user_repository->delete($board_id, $user_id);
    }
}
