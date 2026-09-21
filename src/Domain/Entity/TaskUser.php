<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TaskUser',
    description: 'Represents the association between a task and a user.'
)]
final class TaskUser
{
    public function __construct(
        #[OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: 'f3918a3d-5c74-4b92-8f1e-3a8c7b6d5e4f',
            description: 'Unique identifier for the task-user association.'
        )]
        private readonly string $id,

        #[OA\Property(
            property: 'task_id',
            type: 'string',
            format: 'uuid',
            example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            description: 'Unique identifier of the associated task.'
        )]
        private readonly string $task_id,

        #[OA\Property(
            property: 'user_id',
            type: 'string',
            format: 'uuid',
            example: 'b1c2d3e4-f5g6-7890-hijk-lm1234567890',
            description: 'Unique identifier of the associated user.'
        )]
        private readonly string $user_id,

        #[OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2024-06-01T12:00:00Z',
            description: 'Timestamp when the association was created.'
        )]
        private readonly DateTimeImmutable $created_at
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getTaskId(): string
    {
        return $this->task_id;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }
}
