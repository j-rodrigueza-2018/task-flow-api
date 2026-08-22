<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\GetBoardTasksUseCase;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetBoardTasksUseCaseTest extends TestCase
{
    private TaskRepository&MockObject $task_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private GetBoardTasksUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->task_repository_mock = $this->createMock(TaskRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);

        $this->use_case = new GetBoardTasksUseCase(
            $this->task_repository_mock,
            $this->board_user_repository_mock
        );
    }

    public function testItThrowsExceptionIfUserDoesNotHavePermission(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn(null);

        $this->task_repository_mock
            ->expects($this->never())
            ->method('findByBoardId');

        try {
            $this->use_case->execute($board_id, $user_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('User does not have permission to access this board.', $exception->getMessage());
        }
    }

    public function testItReturnsTasksSuccessfully(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable(),
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn($dummy_board_user);

        $expected_tasks = [];

        $this->task_repository_mock
            ->expects($this->once())
            ->method('findByBoardId')
            ->with($board_id)
            ->willReturn($expected_tasks);

        $result = $this->use_case->execute($board_id, $user_id);

        $this->assertIsArray($result);
        $this->assertSame($expected_tasks, $result);
    }
}
