<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\DeleteBoardUseCase;
use App\Domain\Entity\Board;
use App\Domain\Repository\BoardRepository;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteBoardUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private DeleteBoardUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->use_case = new DeleteBoardUseCase($this->board_repository_mock);
    }

    public function testItThrowsExceptionIfBoardIsNotFound(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn(null);

        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Board not found.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfBoardIsAlreadyDeleted(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);

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

        $this->board_repository_mock
            ->expects($this->never())
            ->method('delete');

        try {
            $this->use_case->execute($board_id);
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $exception) {
            $this->assertEquals('The board is already deleted.', $exception->getMessage());
        }
    }

    public function testItDeletesBoardSuccessfully(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board = new Board(
            id: $board_id,
            name: 'Test Board',
            description: 'This is a test board.',
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        // Mock the findById method to return the dummy board
        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn($dummy_board);

        // Check if the delete method is called with the dummy board
        $this->board_repository_mock
            ->expects($this->once())
            ->method('delete')
            ->with($dummy_board);

        // Execute the use case
        $this->use_case->execute($board_id);
    }
}
