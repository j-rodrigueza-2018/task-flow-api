<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Task;

use App\Application\UseCase\Task\UpdateTaskUseCase;
use App\Domain\Entity\BoardUser;
use App\Domain\Entity\Task;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateTaskUseCaseTest extends TestCase
{
    private TaskRepository&MockObject $task_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private UpdateTaskUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->task_repository_mock = $this->createMock(TaskRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);

        $this->use_case = new UpdateTaskUseCase(
            task_repository: $this->task_repository_mock,
            board_user_repository: $this->board_user_repository_mock
        );
    }

    public function testItThrowsExceptionIfTaskIdIsEmpty(): void
    {
        $this->task_repository_mock
            ->expects($this->never())
            ->method('findById');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        try {
            $this->use_case->execute('', uuid_create(UUID_TYPE_RANDOM), 'Title', null, null, null);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The task_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterIdIsEmpty(): void
    {
        $this->task_repository_mock
            ->expects($this->never())
            ->method('findById');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), '', 'Title', null, null, null);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The requester_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfTaskNotFound(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $this->task_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($task_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        try {
            $this->use_case->execute($task_id, $requester_id, 'Title', null, null, null);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Task not found.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserDoesNotHavePermission(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_task = new Task(
            id: $task_id,
            title: 'Old Title',
            description: null,
            status: Task::STATUS_PENDING,
            board_id: $board_id,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->task_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($task_id)
            ->willReturn($dummy_task);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $requester_id)
            ->willReturn(null);

        $this->task_repository_mock
            ->expects($this->never())
            ->method('save');

        try {
            $this->use_case->execute($task_id, $requester_id, 'New Title', null, null, null);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('User does not have permission to update this task.', $exception->getMessage());
        }
    }

    public function testItUpdatesTaskSuccessfully(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_task = new Task(
            id: $task_id,
            title: 'Old Title',
            description: 'Old Description',
            status: Task::STATUS_PENDING,
            board_id: $board_id,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->task_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($task_id)
            ->willReturn($dummy_task);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $requester_id)
            ->willReturn($dummy_board_user);

        $this->task_repository_mock
            ->expects($this->once())
            ->method('save')
            ->with($this->identicalTo($dummy_task));

        $result = $this->use_case->execute($task_id, $requester_id, 'New Title', 'New Description', Task::STATUS_IN_PROGRESS, null);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('New Title', $result->getTitle());
        $this->assertEquals('New Description', $result->getDescription());
        $this->assertEquals(Task::STATUS_IN_PROGRESS, $result->getStatus());
    }
}
