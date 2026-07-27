<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\BoardUser;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use Override;
use PDO;

final class PostgresBoardUserRepository implements BoardUserRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    public function save(BoardUser $board_user): void
    {
        $stmt = $this->pdo->prepare(<<<EOH
            INSERT INTO board_users (id, board_id, user_id, role, created_at, updated_at)
            VALUES (:id, :board_id, :user_id, :role, :created_at, :updated_at)
            ON CONFLICT (board_id, user_id) DO UPDATE SET
                role = EXCLUDED.role,
                updated_at = EXCLUDED.updated_at
        EOH);

        $stmt->execute([
            'id' => $board_user->getId(),
            'board_id' => $board_user->getBoardId(),
            'user_id' => $board_user->getUserId(),
            'role' => $board_user->getRole()->value,
            'created_at' => $board_user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $board_user->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Override]
    public function delete(string $board_id, string $user_id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM board_users WHERE board_id = :board_id AND user_id = :user_id');
        $stmt->execute([
            'board_id' => $board_id,
            'user_id' => $user_id
        ]);
    }

    public function findByBoardAndUser(string $board_id, string $user_id): ?BoardUser
    {
        $stmt = $this->pdo->prepare('SELECT * FROM board_users WHERE board_id = :board_id AND user_id = :user_id');

        $stmt->execute([
            'board_id' => $board_id,
            'user_id' => $user_id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return new BoardUser(
            id: $row['id'],
            board_id: $row['board_id'],
            user_id: $row['user_id'],
            role: $row['role'],
            created_at: new DateTimeImmutable($row['created_at']),
            updated_at: new DateTimeImmutable($row['updated_at'])
        );
    }
}
