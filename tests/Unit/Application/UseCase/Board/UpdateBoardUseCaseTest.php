<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\UpdateBoardUseCase;
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

final class UpdateBoardUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private UpdateBoardUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);

        $this->use_case = new UpdateBoardUseCase(
            $this->board_repository_mock,
            $this->board_user_repository_mock
        );
    }

    public function testItThrowsExceptionIfBoardIdIsEmpty(): void
    {
        $this->board_repository_mock
            ->expects($this->never())
            ->method('findById');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        try {
            $this->use_case->execute('', uuid_create(UUID_TYPE_RANDOM), 'New Name', 'New Description');
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

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), '', 'New Name', 'New Description');
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
            ->method('save');

        try {
            $this->use_case->execute($board_id, $requester_id, 'New Name Valid', 'New Description');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Board not found.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterDoesNotHavePermission(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board = new Board(
            id: $board_id,
            name: 'Original Name',
            description: 'Original description',
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
            ->method('save');

        try {
            $this->use_case->execute($board_id, $requester_id, 'New Name Valid', 'New Description');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Requester does not have permission to update this board.', $exception->getMessage());
        }
    }

    public function testItDoesNotSaveIfThereAreNoChanges(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board = new Board(
            id: $board_id,
            name: 'Original Name',
            description: 'Original description',
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
            ->method('save');

        $result = $this->use_case->execute($board_id, $requester_id, null, null);

        $this->assertSame($dummy_board, $result);
    }

    public function testItSavesBoardIfOnlyNameIsUpdated(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $updated_name = 'Updated Valid Name';

        $dummy_board = new Board(
            id: $board_id,
            name: 'Original Name',
            description: 'Original description',
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
            ->expects($this->once())
            ->method('save')
            ->with($dummy_board);

        $result = $this->use_case->execute($board_id, $requester_id, $updated_name, null);

        $this->assertEquals($updated_name, $result->getName());
        $this->assertEquals($dummy_board->getDescription(), $result->getDescription());
    }

    public function testItSavesBoardIfOnlyDescriptionIsUpdated(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $updated_description = 'Updated description';

        $dummy_board = new Board(
            id: $board_id,
            name: 'Original Name',
            description: 'Original description',
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
            ->expects($this->once())
            ->method('save')
            ->with($dummy_board);

        $result = $this->use_case->execute($board_id, $requester_id, null, $updated_description);

        $this->assertEquals($dummy_board->getName(), $result->getName());
        $this->assertEquals($updated_description, $result->getDescription());
    }

    public function testItSavesBoardIfBothFieldsAreUpdated(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $updated_name = 'Updated Valid Name';
        $updated_description = 'Updated description';

        $dummy_board = new Board(
            id: $board_id,
            name: 'Original Name',
            description: 'Original description',
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
            ->expects($this->once())
            ->method('save')
            ->with($dummy_board);

        $result = $this->use_case->execute($board_id, $requester_id, $updated_name, $updated_description);

        $this->assertEquals($updated_name, $result->getName());
        $this->assertEquals($updated_description, $result->getDescription());
    }
}
