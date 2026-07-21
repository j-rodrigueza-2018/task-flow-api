<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Entity\Board;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use DateTimeImmutable;

final class CreateBoardUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository
    ) {}

    public function execute(string $user_id, string $name, ?string $description = null): Board
    {
        $board = new Board(
            id: uuid_create(UUID_TYPE_RANDOM),
            name: $name,
            description: $description,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_repository->save($board);

        $board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board->getId(),
            user_id: $user_id,
            role: BoardRole::OWNER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_repository->addUserToBoard($board_user);

        return $board;
    }
}
