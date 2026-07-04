<?php

declare(strict_types=1);

namespace App\Application\UseCase\User;

use App\Domain\Repository\UserRepository;
use Firebase\JWT\JWT;

final class LoginUserUseCase
{
    public function __construct(
        private readonly UserRepository $user_repository,
        private readonly string $jwt_secret
    ) {}

    public function execute(string $email, string $password): string {
        $user = $this->user_repository->findByEmail($email);

        if ($user === null || !password_verify($password, $user->getPasswordHash())) {
            throw new \InvalidArgumentException('Invalid email or password.');
        }

        $issued_at = time();
        $expiration_time = $issued_at + 3600; // Token valid for 1 hour

        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => $issued_at,
            'exp' => $expiration_time
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }
}
