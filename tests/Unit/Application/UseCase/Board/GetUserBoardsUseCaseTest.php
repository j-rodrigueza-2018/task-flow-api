<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\GetUserBoardsUseCase;
use App\Domain\Entity\Board;
use App\Domain\Repository\BoardRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetUserBoardsUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private GetUserBoardsUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->use_case = new GetUserBoardsUseCase($this->board_repository_mock);
    }

    public function testItThrowsExceptionIfRequesterIdIsEmpty(): void
    {
        $this->board_repository_mock
            ->expects($this->never())
            ->method('findByUserId');

        try {
            $this->use_case->execute('');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('The requester_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItReturnsUserBoardsSuccessfully(): void
    {
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $expected_boards = [
            new Board(
                id: uuid_create(UUID_TYPE_RANDOM),
                name: 'Test Board',
                description: 'This is a test board.',
                created_at: new DateTimeImmutable(),
                updated_at: new DateTimeImmutable()
            )
        ];

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findByUserId')
            ->with($requester_id)
            ->willReturn($expected_boards);

        $result = $this->use_case->execute($requester_id);

        $this->assertIsArray($result);
        $this->assertSame($expected_boards, $result);
    }
}
