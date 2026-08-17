<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\User;

use App\Application\UseCase\User\LoginUserUseCase;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LoginUserUseCaseTest extends TestCase
{
    private UserRepository&MockObject $user_repository_mock;
    private LoginUserUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->user_repository_mock = $this->createMock(UserRepository::class);
        $this->use_case = new LoginUserUseCase(
            $this->user_repository_mock,
            getenv('JWT_SECRET') ?: 'very_long_secret_key_of_32_chars'
        );
    }

    public function testItThrowsExceptionIfEmailIsNotFound(): void
    {
        $this->user_repository_mock
            ->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn(null);

        try {
            $this->use_case->execute('test@example.com', 'password123');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Invalid email or password.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfPasswordIsIncorrect(): void
    {
        $dummy_user = new User(
            id: uuid_create(UUID_TYPE_RANDOM),
            nickname: 'nickname',
            email: 'test@example.com',
            password_hash: password_hash('correct_password', PASSWORD_BCRYPT),
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->user_repository_mock
            ->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($dummy_user);

        try {
            $this->use_case->execute('test@example.com', 'wrong_password');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Invalid email or password.', $exception->getMessage());
        }
    }

    public function testItReturnsTokenOnSuccessfulLogin(): void
    {
        $raw_password = 'secure_password';

        $dummy_user = new User(
            id: uuid_create(UUID_TYPE_RANDOM),
            nickname: 'nickname',
            email: 'test@example.com',
            password_hash: password_hash($raw_password, PASSWORD_BCRYPT),
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->user_repository_mock
            ->expects($this->once())
            ->method('findByEmail')
            ->with('test@example.com')
            ->willReturn($dummy_user);

        $token = $this->use_case->execute('test@example.com', $raw_password);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        $this->assertCount(3, explode('.', $token));
    }
}
