<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\User;

use App\Application\UseCase\User\RegisterUserUseCase;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class RegisterUserUseCaseTest extends TestCase
{
    private UserRepository&MockObject $user_repository_mock;
    private RegisterUserUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->user_repository_mock = $this->createMock(UserRepository::class);
        $this->use_case = new RegisterUserUseCase($this->user_repository_mock);
    }

    public function test_it_throws_exception_if_password_is_too_short(): void
    {
        try {
            $this->use_case->execute('nickname', 'test@example.com', 'short');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('The password must be at least 8 characters long.', $e->getMessage());
        }
    }

    public function test_it_throws_exception_if_email_already_exists(): void
    {
        $dummy_user = new User(
            uuid_create(UUID_TYPE_RANDOM),
            'nickname',
            'test@example.com',
            'hash_password',
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $this->user_repository_mock
            ->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($dummy_user);

        try {
            $this->use_case->execute('nickname', 'test@example.com', 'password123');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('A user with this email already exists.', $e->getMessage());
        }
    }

    public function test_it_registers_a_user_successfully(): void
    {
        $this->user_repository_mock
            ->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        $this->user_repository_mock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $this->use_case->execute('nickname', 'test@example.com', 'password123');
    }
}
