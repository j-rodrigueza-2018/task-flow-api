<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepository
{
    public function save(User $user): void;
    public function delete(User $user): void;
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
}
