<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Board;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use DateTimeImmutable;
use Override;
use PDO;

final class PostgresBoardRepository implements BoardRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    #[Override]
    public function save(Board $board): void
    {
        $stmt = $this->pdo->prepare(<<<EOH
            INSERT INTO boards (id, name, description, created_at, updated_at, deleted_at)
            VALUES (:id, :name, :description, :created_at, :updated_at, :deleted_at)
            ON CONFLICT (id) DO UPDATE SET
                name = EXCLUDED.name,
                description = EXCLUDED.description,
                updated_at = EXCLUDED.updated_at,
                deleted_at = EXCLUDED.deleted_at
        EOH);

        $stmt->execute([
            ':id' => $board->getId(),
            ':name' => $board->getName(),
            ':description' => $board->getDescription(),
            ':created_at' => $board->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $board->getUpdatedAt()->format('Y-m-d H:i:s'),
            ':deleted_at' => $board->isDeleted() ? $board->getDeletedAt()->format('Y-m-d H:i:s') : null,
        ]);
    }

    #[Override]
    public function delete(Board $board): void
    {
        $stmt = $this->pdo->prepare('UPDATE boards SET updated_at = :updated_at, deleted_at = :deleted_at WHERE id = :id');
        $stmt->execute([
            ':id' => $board->getId(),
            ':updated_at' => $board->getUpdatedAt()->format('Y-m-d H:i:s'),
            ':deleted_at' => $board->getDeletedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Override]
    public function findById(string $id): ?Board
    {
        $stmt = $this->pdo->prepare('SELECT * FROM boards WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    #[Override]
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM boards WHERE deleted_at IS NULL ORDER BY created_at DESC');

        $boards = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $boards[] = $this->hydrate($row);
        }

        return $boards;
    }

    #[Override]
    public function addUserToBoard(string $board_id, string $user_id, BoardRole $role): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(<<<EOH
            INSERT INTO board_users (id, board_id, user_id, role, created_at, updated_at) 
            VALUES (:id, :board_id, :user_id, :role, :created_at, :updated_at) 
            ON CONFLICT (board_id, user_id) DO UPDATE SET 
                role = EXCLUDED.role,
                updated_at = EXCLUDED.updated_at
        EOH);

        $stmt->execute([
            ':id' => uuid_create(UUID_TYPE_RANDOM),
            ':board_id' => $board_id,
            ':user_id' => $user_id,
            ':role' => $role->value,
            ':created_at' => $now,
            ':updated_at' => $now
        ]);
    }

    /**
     * Hydrates a Board entity from a database row.
     * 
     * @param array $row_data The database row data.
     * 
     * @return Board The hydrated Board entity.
     */
    private function hydrate(array $row_data): Board
    {
        return new Board(
            id: $row_data['id'],
            name: $row_data['name'],
            description: $row_data['description'],
            created_at: new DateTimeImmutable($row_data['created_at']),
            updated_at: new DateTimeImmutable($row_data['updated_at']),
            deleted_at: isset($row_data['deleted_at']) ? new DateTimeImmutable($row_data['deleted_at']) : null
        );
    }
}
