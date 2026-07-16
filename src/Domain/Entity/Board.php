<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class Board
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private ?string $description,
        private DateTimeImmutable $created_at,
        private DateTimeImmutable $updated_at,
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

        if (strlen($name) < 10 || strlen($name) > 255) {
            throw new InvalidArgumentException('The name must be between 10 and 255 characters long.');
        }
    }

    private function markAsUpdated(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }
}
