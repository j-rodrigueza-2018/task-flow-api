<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class Task
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    private const ALLOWED_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
    ];

    public function __construct(
        private readonly string $id,
        private readonly string $user_id,
        private string $title,
        private ?string $description,
        private string $status,
        private string $board_id,
        private readonly DateTimeImmutable $created_at,
        private DateTimeImmutable $updated_at,
        private ?DateTimeImmutable $deleted_at = null
    ) {
        $this->validateTitle($title);
        $this->validateStatus($status);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getBoardId(): string
    {
        return $this->board_id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    public function updateTitle(string $title): void
    {
        $this->validateTitle($title);

        $this->title = $title;
        $this->markAsUpdated();
    }

    public function updateDescription(?string $description): void
    {
        $this->description = $description;
        $this->markAsUpdated();
    }

    public function updateStatus(string $status): void
    {
        $this->validateStatus($status);

        $this->status = $status;
        $this->markAsUpdated();
    }

    public function moveToBoard(string $board_id): void
    {
        if (trim($board_id) === '') {
            throw new InvalidArgumentException('The board ID cannot be empty.');
        }

        $this->board_id = $board_id;
        $this->markAsUpdated();
    }

    public function delete(): void
    {
        if ($this->isDeleted()) {
            throw new DomainException('The task is already deleted.');
        }

        $this->deleted_at = new DateTimeImmutable();
        $this->markAsUpdated();
    }

    private function validateTitle(string $title): void
    {
        if (trim($title) === '') {
            throw new InvalidArgumentException('The title cannot be empty.');
        }

        if (strlen($title) < 3 || strlen($title) > 255) {
            throw new InvalidArgumentException('The title must be between 3 and 255 characters long.');
        }
    }

    private function validateStatus(string $status): void
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException('The status must be one of the allowed statuses: ' . implode(', ', self::ALLOWED_STATUSES));
        }
    }

    private function markAsUpdated(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }
}
