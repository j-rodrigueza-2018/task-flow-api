<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Task;

use App\Application\UseCase\Task\AddUserToTaskUseCase;
use App\Domain\Entity\BoardUser;
use App\Domain\Entity\Task;
use App\Domain\Entity\TaskUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardUserRepository;
use App\Domain\Repository\TaskRepository;
use App\Domain\Repository\TaskUserRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddUserToTaskUseCaseTest extends TestCase
{
    private TaskRepository&MockObject $task_repository_mock;
    private TaskUserRepository&MockObject $task_user_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private AddUserToTaskUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->task_repository_mock = $this->createMock(TaskRepository::class);
        $this->task_user_repository_mock = $this->createMock(TaskUserRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);

        $this->use_case = new AddUserToTaskUseCase(
            task_repository: $this->task_repository_mock,
            task_user_repository: $this->task_user_repository_mock,
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

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('findByTaskAndUser');

        try {
            $this->use_case->execute('', uuid_create(UUID_TYPE_RANDOM), uuid_create(UUID_TYPE_RANDOM));
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The task_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserIdIsEmpty(): void
    {
        $this->task_repository_mock
            ->expects($this->never())
            ->method('findById');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('findByTaskAndUser');

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), '', uuid_create(UUID_TYPE_RANDOM));
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The user_id cannot be empty.', $exception->getMessage());
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

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('findByTaskAndUser');

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), uuid_create(UUID_TYPE_RANDOM), '');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The requester_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfTaskDoesNotExist(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $this->task_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($task_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('findByTaskAndUser');

        try {
            $this->use_case->execute($task_id, $user_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The task does not exist.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterDoesNotHavePermission(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_task = new Task(
            id: $task_id,
            title: 'Test Task',
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

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('findByTaskAndUser');

        try {
            $this->use_case->execute($task_id, $user_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('You do not have permission to assign users to this task.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserDoesNotBelongToBoard(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_task = new Task(
            id: $task_id,
            title: 'Test Task',
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

        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(
                function (string $b_id, string $u_id) use ($board_id, $requester_id, $dummy_board_user) {
                    if ($b_id === $board_id && $u_id === $requester_id) {
                        return $dummy_board_user;
                    }
                    return null;
                }
            );

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('findByTaskAndUser');

        try {
            $this->use_case->execute($task_id, $user_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The user must be a member of the board to be assigned to its tasks.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserAlreadyBelongsToTask(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_task = new Task(
            id: $task_id,
            title: 'Test Task',
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

        $dummy_board_requester_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_board_new_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(
                function (string $b_id, string $u_id) use ($board_id, $requester_id, $dummy_board_requester_user, $user_id, $dummy_board_new_user) {
                    if ($b_id === $board_id && $u_id === $requester_id) {
                        return $dummy_board_requester_user;
                    }

                    if ($b_id === $board_id && $u_id === $user_id) {
                        return $dummy_board_new_user;
                    }

                    return null;
                }
            );

        $dummy_task_user = new TaskUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            task_id: $task_id,
            user_id: $user_id,
            created_at: new DateTimeImmutable()
        );

        $this->task_user_repository_mock
            ->expects($this->once())
            ->method('findByTaskAndUser')
            ->with($task_id, $user_id)
            ->willReturn($dummy_task_user);

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('save');

        try {
            $this->use_case->execute($task_id, $user_id, $requester_id);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The user is already assigned to this task.', $exception->getMessage());
        }
    }

    public function testItAddsUserToTaskSuccessfully(): void
    {
        $task_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_task = new Task(
            id: $task_id,
            title: 'Test Task',
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

        $dummy_board_requester_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $requester_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $dummy_board_new_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->exactly(2))
            ->method('findByBoardAndUser')
            ->willReturnCallback(
                function (string $b_id, string $u_id) use ($board_id, $requester_id, $dummy_board_requester_user, $user_id, $dummy_board_new_user) {
                    if ($b_id === $board_id && $u_id === $requester_id) {
                        return $dummy_board_requester_user;
                    }

                    if ($b_id === $board_id && $u_id === $user_id) {
                        return $dummy_board_new_user;
                    }

                    return null;
                }
            );

        $this->task_user_repository_mock
            ->expects($this->once())
            ->method('findByTaskAndUser')
            ->with($task_id, $user_id)
            ->willReturn(null);

        $this->task_user_repository_mock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(TaskUser::class));

        $result = $this->use_case->execute($task_id, $user_id, $requester_id);

        $this->assertInstanceOf(TaskUser::class, $result);
        $this->assertEquals($task_id, $result->getTaskId());
        $this->assertEquals($user_id, $result->getUserId());
    }
}
