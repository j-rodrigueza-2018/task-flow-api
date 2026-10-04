<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    description: 'Represents a user entity in the system.'
)]
final class User
{
    public function __construct(
        #[OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: 'c9f87a32-1a45-4c67-8b9a-1c2d3e4f5a6b',
            description: 'Unique identifier for the user.'
        )]
        private readonly string $id,

        #[OA\Property(
            property: 'nickname',
            type: 'string',
            example: 'johndoe',
            description: 'The display name of the user (between 4 and 20 characters).'
        )]
        private string $nickname,

        #[OA\Property(
            property: 'email',
            type: 'string',
            format: 'email',
            example: 'john.doe@example.com',
            description: 'The user email address.'
        )]
        private string $email,

        // We do not expose the password hash in the OpenAPI documentation for security reasons.
        private string $password_hash,

        #[OA\Property(
            property: 'created_at',
            type: 'string',
            format: 'date-time',
            example: '2024-06-01T12:00:00Z',
            description: 'The timestamp when the user was registered.'
        )]
        private readonly DateTimeImmutable $created_at,

        #[OA\Property(
            property: 'updated_at',
            type: 'string',
            format: 'date-time',
            example: '2024-06-02T15:30:00Z',
            description: 'The timestamp when the user profile was last updated.'
        )]
        private DateTimeImmutable $updated_at
    ) {
        if (trim($nickname) === '') {
            throw new InvalidArgumentException('The nickname cannot be empty.');
        }

        if (mb_strlen($nickname) < 4 || mb_strlen($nickname) > 20) {
            throw new InvalidArgumentException('The nickname must be between 4 and 20 characters long.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The email is not valid.');
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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }
}
