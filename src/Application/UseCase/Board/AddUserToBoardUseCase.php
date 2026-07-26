<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class AddUserToBoardUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $board_id, string $user_id, BoardRole $board_role): BoardUser
    {
        $board = $this->board_repository->findById($board_id);
        if (!$board) {
            throw new InvalidArgumentException('Board not found.');
        }

        $existing_member = $this->board_user_repository->findByBoardAndUser($board_id, $user_id);
        if ($existing_member) {
            throw new DomainException('The user is already a member of this board.');
        }

        $board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: $board_role,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository->save($board_user);

        return $board_user;
    }
}
