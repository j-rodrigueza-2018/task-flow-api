<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\BoardRole;
use DateTimeImmutable;

final class BoardUser
{
    public function __construct(
        private readonly string $id,
        private readonly string $board_id,
        private readonly string $user_id,
        private BoardRole $role,
        private readonly DateTimeImmutable $created_at,
        private DateTimeImmutable $updated_at
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getBoardId(): string
    {
        return $this->board_id;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function getRole(): BoardRole
    {
        return $this->role;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function changeRole(BoardRole $new_role): void
    {
        if ($this->role === $new_role) {
            return;
        }

        $this->role = $new_role;
        $this->updated_at = new DateTimeImmutable();
    }
}
