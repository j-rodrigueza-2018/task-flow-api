<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use InvalidArgumentException;

final class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(string $nickname, string $email, string $password): void
    {
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('The password must be at least 8 characters long.');
        }

        $existing_user = $this->userRepository->findByEmail($email);
        if ($existing_user !== null) {
            throw new InvalidArgumentException('A user with this email already exists.');
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $id = uuid_create(UUID_TYPE_RANDOM);
        $current_date = new \DateTimeImmutable();

        $user = new User(
            $id,
            $nickname,
            $email,
            $password_hash,
            $current_date,
            $current_date
        );

        $this->userRepository->save($user);
    }
}
