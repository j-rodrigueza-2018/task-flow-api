<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Entity\Board;
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

        $this->board_repository->addUserToBoard($board->getId(), $user_id, BoardRole::OWNER);

        return $board;
    }
}
