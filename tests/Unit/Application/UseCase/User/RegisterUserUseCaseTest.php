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

    public function testItThrowsExceptionIfPasswordIsTooShort(): void
    {
        try {
            $this->use_case->execute('nickname', 'test@example.com', 'short');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The password must be at least 8 characters long.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfEmailAlreadyExists(): void
    {
        $dummy_user = new User(
            id: uuid_create(UUID_TYPE_RANDOM),
            nickname: 'nickname',
            email: 'test@example.com',
            password_hash: 'hash_password',
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable()
        );

        $this->user_repository_mock
            ->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($dummy_user);

        try {
            $this->use_case->execute('nickname', 'test@example.com', 'password123');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('A user with this email already exists.', $exception->getMessage());
        }
    }

    public function testItRegistersAUserSuccessfully(): void
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
