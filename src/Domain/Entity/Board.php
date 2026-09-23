<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Board',
    description: 'Represents a board entity.'
)]
final class Board
{
    public function __construct(
        #[OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            description: 'Unique identifier for the board.'
        )]
        private readonly string $id,

        #[OA\Property(
            property: 'name',
            type: 'string',
            example: 'Project Management Board',
            description: 'Name of the board.'
        )]
        private string $name,

        #[OA\Property(
            property: 'description',
            type: 'string',
            nullable: true,
            example: 'This board is used for managing project tasks and milestones.',
            description: 'Optional description of the board.'
        )]
        private ?string $description,

        #[OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2024-06-01T12:00:00Z',
            description: 'The timestamp when the board was created.'
        )]
        private DateTimeImmutable $created_at,

        #[OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2024-06-02T15:30:00Z',
            description: 'The timestamp when the board was last updated.'
        )]
        private DateTimeImmutable $updated_at,

        #[OA\Property(
            property: 'deleted_at',
            type: 'string',
            format: 'date-time',
            nullable: true,
            example: '2024-06-03T10:15:00Z',
            description: 'The timestamp when the board was deleted, if applicable.'
        )]
        private ?DateTimeImmutable $deleted_at = null
    ) {
        $this->validateName($name);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
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

    public function updateName(string $name): void
    {
        $this->validateName($name);

        $this->name = $name;
        $this->markAsUpdated();
    }

    public function updateDescription(?string $description): void
    {
        $this->description = $description;
        $this->markAsUpdated();
    }

    public function delete(): void
    {
        if ($this->isDeleted()) {
            throw new DomainException('The board is already deleted.');
        }

        $this->deleted_at = new DateTimeImmutable();
        $this->markAsUpdated();
    }

    private function validateName(string $name): void
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('The name cannot be empty.');
        }

        if (mb_strlen($name) < 10 || mb_strlen($name) > 255) {
            throw new InvalidArgumentException('The name must be between 10 and 255 characters long.');
        }
    }

    private function markAsUpdated(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }
}
