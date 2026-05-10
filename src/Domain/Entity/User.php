<?php

declare(strict_types=1);

namespace App\Domain\Entity;

final class User
{
    public function __construct(
        private readonly string $id,
        private string $nickname,
        private string $email,
        private string $password_hash,
        private readonly \DateTimeImmutable $created_at,
        private \DateTimeImmutable $updated_at
    ) {
        if (trim($nickname) === '') {
            throw new \InvalidArgumentException('The nickname cannot be empty.');
        }

        if (strlen($nickname) < 4 || strlen($nickname) > 20) {
            throw new \InvalidArgumentException('The nickname must be between 4 and 20 characters long.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The email is not valid.');
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updated_at;
    }
}
