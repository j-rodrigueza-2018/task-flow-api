<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\GetBoardUseCase;
use App\Domain\Entity\Board;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetBoardUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private GetBoardUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);

        $this->use_case = new GetBoardUseCase(
            $this->board_repository_mock,
            $this->board_user_repository_mock
        );
    }

    public function testItThrowsExceptionIfBoardIsNotFound(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        try {
            $this->use_case->execute($board_id, $user_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Board not found.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserDoesNotHavePermission(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board = new Board(
            id: $board_id,
            name: 'Test Board',
            description: 'This is a test board.',
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn($dummy_board);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn(null);

        try {
            $this->use_case->execute($board_id, $user_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('User does not have permission to access this board.', $exception->getMessage());
        }
    }

    public function testItReturnsBoardSuccessfully(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board = new Board(
            id: $board_id,
            name: 'Test Board',
            description: 'This is a test board.',
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn($dummy_board);

        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn($dummy_board_user);

        $result = $this->use_case->execute($board_id, $user_id);

        $this->assertInstanceOf(Board::class, $result);
        $this->assertSame($dummy_board, $result);
    }
}
