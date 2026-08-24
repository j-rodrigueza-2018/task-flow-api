<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Task;

use App\Application\UseCase\Task\CreateTaskUseCase;
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

final class CreateTaskUseCaseTest extends TestCase
{
    private TaskRepository&MockObject $task_repository_mock;
    private TaskUserRepository&MockObject $task_user_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private CreateTaskUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->task_repository_mock = $this->createMock(TaskRepository::class);
        $this->task_user_repository_mock = $this->createMock(TaskUserRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);

        $this->use_case = new CreateTaskUseCase(
            task_repository: $this->task_repository_mock,
            task_user_repository: $this->task_user_repository_mock,
            board_user_repository: $this->board_user_repository_mock
        );
    }

    public function testItThrowsExceptionIfUserDoesNotBelongToBoard(): void
    {
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn(null);

        $this->task_repository_mock
            ->expects($this->never())
            ->method('save');

        $this->task_user_repository_mock
            ->expects($this->never())
            ->method('save');

        try {
            $this->use_case->execute($user_id, $board_id, 'New Task');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('User does not have permission to create tasks in this board.', $exception->getMessage());
        }
    }

    public function testItCreatesTaskSuccessfully(): void
    {
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $title = 'New Task';
        $description = 'This is the description';

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

        $this->task_repository_mock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Task::class));

        $this->task_user_repository_mock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(TaskUser::class));

        $result = $this->use_case->execute($user_id, $board_id, $title, $description);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals($title, $result->getTitle());
        $this->assertEquals($description, $result->getDescription());
        $this->assertEquals(Task::STATUS_PENDING, $result->getStatus());
    }
}
