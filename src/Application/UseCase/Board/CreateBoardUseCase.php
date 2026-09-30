<?php

declare(strict_types=1);

namespace App\Application\UseCase\Board;

use App\Domain\Entity\Board;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final class CreateBoardUseCase
{
    public function __construct(
        private readonly BoardRepository $board_repository,
        private readonly BoardUserRepository $board_user_repository
    ) {}

    public function execute(string $requester_id, string $name, ?string $description = null): Board
    {
        if (empty($requester_id)) {
            throw new InvalidArgumentException('The requester_id cannot be empty.');
        }

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
            user_id: $requester_id,
            role: BoardRole::OWNER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository->save($board_user);

        return $board;
    }
}
