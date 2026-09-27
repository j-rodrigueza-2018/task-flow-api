<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\DeleteBoardUseCase;
use App\Domain\Entity\Board;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteBoardUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private DeleteBoardUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);
        $this->use_case = new DeleteBoardUseCase($this->board_repository_mock, $this->board_user_repository_mock);
    }

    public function testItThrowsExceptionIfBoardIdIsEmpty(): void
    {
        $this->board_repository_mock
            ->expects($this->never())
            ->method('findById');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute('', uuid_create(UUID_TYPE_RANDOM));
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The board_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterIdIsEmpty(): void
    {
        $this->board_repository_mock
            ->expects($this->never())
            ->method('findById');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');
            
        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), '');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The requester_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfBoardIsNotFound(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Board not found.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserIsMemberButNotOwner(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

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
            user_id: $requester_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $requester_id)
            ->willReturn($dummy_board_user);

        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Requester does not have permission to delete this board.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserDoesNotHavePermission(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

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
            ->with($board_id, $requester_id)
            ->willReturn(null);

        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Requester does not have permission to delete this board.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfBoardIsAlreadyDeleted(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board = new Board(
            id: $board_id,
            name: 'Test Board',
            description: 'This is a test board.',
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        // Simulate the board being already deleted
        $dummy_board->delete();

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn($dummy_board);

        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::OWNER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $requester_id)
            ->willReturn($dummy_board_user);

        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id, $requester_id);
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $exception) {
            $this->assertEquals('The board is already deleted.', $exception->getMessage());
        }
    }

    public function testItDeletesBoardSuccessfully(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

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
            user_id: $requester_id,
            role: BoardRole::OWNER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $requester_id)
            ->willReturn($dummy_board_user);

        $this->board_repository_mock
            ->expects($this->once())
            ->method('delete')
            ->with($dummy_board);

        $this->use_case->execute($board_id, $requester_id);
    }
}
