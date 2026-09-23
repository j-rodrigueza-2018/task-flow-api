<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\BoardRole;
use DateTimeImmutable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BoardUser',
    description: 'Represents the association and role of a user within a board.'
)]
final class BoardUser
{
    public function __construct(
        #[OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: 'f3918a3d-5c74-4b92-8f1e-3a8c7b6d5e4f',
            description: 'Unique identifier for the board-user association.'
        )]
        private readonly string $id,

        #[OA\Property(
            property: 'board_id',
            type: 'string',
            format: 'uuid',
            example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            description: 'Unique identifier of the associated board.'
        )]
        private readonly string $board_id,

        #[OA\Property(
            property: 'user_id',
            type: 'string',
            format: 'uuid',
            example: 'b1c2d3e4-f5g6-7890-hijk-lm1234567890',
            description: 'Unique identifier of the associated user.'
        )]
        private readonly string $user_id,

        #[OA\Property(
            property: 'role',
            type: 'string',
            enum: BoardRole::class,
            example: BoardRole::MEMBER,
            description: 'The role of the user within the board (e.g., MEMBER, ADMIN).'
        )]
        private BoardRole $role,

        #[OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2024-06-01T12:00:00Z',
            description: 'Timestamp when the association was created.'
        )]
        private readonly DateTimeImmutable $created_at,

        #[OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2024-06-02T15:30:00Z',
            description: 'Timestamp when the association was last updated.'
        )]
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
