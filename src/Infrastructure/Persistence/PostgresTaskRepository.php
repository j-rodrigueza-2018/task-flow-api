<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Task;
use App\Domain\Repository\TaskRepository;
use DateTimeImmutable;
use Override;
use PDO;

final class PostgresTaskRepository implements TaskRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    #[Override]
    public function save(Task $task): void
    {
        $stmt = $this->pdo->prepare(<<<EOH
            INSERT INTO tasks (id, user_id, title, description, status, created_at, updated_at)
            VALUES (:id, :user_id, :title, :description, :status, :created_at, :updated_at)
            ON CONFLICT (id) DO UPDATE SET
                title = EXCLUDED.title,
                description = EXCLUDED.description,
                status = EXCLUDED.status,
                updated_at = EXCLUDED.updated_at
        EOH);

        $stmt->execute([
            ':id' => $task->getId(),
            ':user_id' => $task->getUserId(),
            ':title' => $task->getTitle(),
            ':description' => $task->getDescription(),
            ':status' => $task->getStatus(),
            ':created_at' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Override]
    public function delete(Task $task): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $task->getId()]);
    }

    #[Override]
    public function findById(string $id): ?Task
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    #[Override]
    public function findByUserId(string $user_id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tasks WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user_id]);

        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = $this->hydrate($row);
        }

        return $tasks;
    }

    /**
     * Hydrate a Task entity from a database row.
     * 
     * @param array $row_data The associative array representing a database row.
     * 
     * @return Task The hydrated Task entity.
     */
    private function hydrate(array $row_data): Task
    {
        return new Task(
            $row_data['id'],
            $row_data['user_id'],
            $row_data['title'],
            $row_data['description'],
            $row_data['status'],
            new DateTimeImmutable($row_data['created_at']),
            new DateTimeImmutable($row_data['updated_at'])
        );
    }
}
