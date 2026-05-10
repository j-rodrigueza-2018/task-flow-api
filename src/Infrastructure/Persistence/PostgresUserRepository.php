<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use DateTimeImmutable;
use Override;
use PDO;

final class PostgresUserRepository implements UserRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {}

    #[Override]
    public function save(User $user): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (id, nickname, email, password_hash, created_at, updated_at)
            VALUES (:id, :nickname, :email, :password_hash, :created_at, :updated_at)
            ON CONFLICT (id) DO UPDATE SET
                nickname = EXCLUDED.nickname,
                email = EXCLUDED.email,
                password_hash = EXCLUDED.password_hash,
                updated_at = EXCLUDED.updated_at
        ');

        $stmt->execute([
            ':id' => $user->getId(),
            ':nickname' => $user->getNickname(),
            ':email' => $user->getEmail(),
            ':password_hash' => $user->getPasswordHash(),
            ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Override]
    public function delete(User $user): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $user->getId()]);
    }

    #[Override]
    public function findById(string $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    #[Override]
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Hydrate a User entity from a database row.
     * 
     * @param array $row_data The associative array representing a database row.
     * 
     * @return User The hydrated User entity.
     */
    private function hydrate(array $row_data): User
    {
        return new User(
            $row_data['id'],
            $row_data['nickname'],
            $row_data['email'],
            $row_data['password_hash'],
            new DateTimeImmutable($row_data['created_at']),
            new DateTimeImmutable($row_data['updated_at'])
        );
    }
}
