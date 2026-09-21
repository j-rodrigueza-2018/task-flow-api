<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Task',
    description: 'Represents a task in the system.',
)]
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
        #[OA\Property(
            description: 'The unique identifier of the task.',
            format: 'uuid',
            example: 'cfca3139-dfff-49bf-a1df-8baa8e392d01'
        )]
        private readonly string $id,

        #[OA\Property(
            description: 'The title of the task.',
            example: 'Implement user authentication'
        )]
        private string $title,

        #[OA\Property(
            description: 'The description of the task.',
            example: 'Implement user authentication using JWT tokens.'
        )]
        private ?string $description,

        #[OA\Property(
            description: 'The status of the task.',
            enum: [self::STATUS_PENDING, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED],
            example: self::STATUS_IN_PROGRESS
        )]
        private string $status,

        #[OA\Property(
            description: 'The unique identifier of the board to which the task belongs.',
            format: 'uuid',
            example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
        )]
        private string $board_id,

        #[OA\Property(
            description: 'The timestamp when the task was created.',
            format: 'date-time',
            example: '2024-06-01T12:00:00Z'
        )]
        private readonly DateTimeImmutable $created_at,

        #[OA\Property(
            description: 'The timestamp when the task was last updated.',
            format: 'date-time',
            example: '2024-06-02T15:30:00Z'
        )]
        private DateTimeImmutable $updated_at,

        #[OA\Property(
            description: 'The timestamp when the task was deleted, if applicable.',
            format: 'date-time',
            example: '2024-06-03T10:15:00Z',
            nullable: true
        )]
        private ?DateTimeImmutable $deleted_at = null
    ) {
        $this->validateTitle($title);
        $this->validateStatus($status);
        $this->validateBoardId($board_id);
    }

    public function getId(): string
    {
        return $this->id;
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
        $this->validateBoardId($board_id);

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

        if (mb_strlen($title) < 3 || mb_strlen($title) > 255) {
            throw new InvalidArgumentException('The title must be between 3 and 255 characters long.');
        }
    }

    private function validateStatus(string $status): void
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException('The status must be one of the allowed statuses: ' . implode(', ', self::ALLOWED_STATUSES));
        }
    }

    private function validateBoardId(string $board_id): void
    {
        if (trim($board_id) === '') {
            throw new InvalidArgumentException('The board ID cannot be empty.');
        }
    }

    private function markAsUpdated(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }
}
