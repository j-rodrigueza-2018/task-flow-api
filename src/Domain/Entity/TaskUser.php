<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;

final class TaskUser
{
    public function __construct(
        private readonly string $id,
        private readonly string $task_id,
        private readonly string $user_id,
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
