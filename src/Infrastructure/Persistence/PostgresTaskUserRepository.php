<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\TaskUser;
use App\Domain\Repository\TaskUserRepository;
use DateTimeImmutable;
use Override;
use PDO;

final class PostgresTaskUserRepository implements TaskUserRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    #[Override]
    public function save(TaskUser $task_user): void
    {
        $stmt = $this->pdo->prepare(<<<EOH
            INSERT INTO task_users (id, task_id, user_id, created_at)
            VALUES (:id, :task_id, :user_id, :created_at)
            ON CONFLICT (task_id, user_id) DO NOTHING
        EOH);

        $stmt->execute([
            'id' => $task_user->getId(),
            'task_id' => $task_user->getTaskId(),
            'user_id' => $task_user->getUserId(),
            'created_at' => $task_user->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Override]
    public function delete(string $task_id, string $user_id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM task_users WHERE task_id = :task_id AND user_id = :user_id');

        $stmt->execute([
            'task_id' => $task_id,
            'user_id' => $user_id
        ]);
    }

    #[Override]
    public function findByTaskAndUser(string $task_id, string $user_id): ?TaskUser
    {
        $stmt = $this->pdo->prepare('SELECT * FROM task_users WHERE task_id = :task_id AND user_id = :user_id');

        $stmt->execute([
            'task_id' => $task_id,
            'user_id' => $user_id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return new TaskUser(
            id: $row['id'],
            task_id: $row['task_id'],
            user_id: $row['user_id'],
            created_at: new DateTimeImmutable($row['created_at'])
        );
    }
}
